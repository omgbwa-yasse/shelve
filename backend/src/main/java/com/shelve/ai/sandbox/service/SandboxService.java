package com.shelve.ai.sandbox.service;

import com.shelve.ai.sandbox.config.SandboxProperties;
import com.shelve.ai.sandbox.entity.AiSandbox;
import com.shelve.ai.sandbox.entity.AiSandboxFile;
import com.shelve.ai.sandbox.repository.AiSandboxFileRepository;
import com.shelve.ai.sandbox.repository.AiSandboxRepository;
import com.shelve.exception.ApiException;
import java.io.File;
import java.io.IOException;
import java.io.UncheckedIOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.time.Duration;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.HexFormat;
import java.util.List;
import java.util.Map;
import java.util.Random;
import java.util.concurrent.TimeUnit;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** Cycle de vie d'un sandbox Python (D14) — parité avec `App\Services\AI\SandboxService` (Laravel). */
@Service
public class SandboxService {
  public static final List<String> ZONES = List.of("input", "core", "reference", "output", "logs");
  public static final List<String> WRITE_ZONES = List.of("input", "core", "reference");

  private final SandboxProperties props;
  private final AiSandboxRepository sandboxRepository;
  private final AiSandboxFileRepository fileRepository;
  private final Random random = new Random();

  public SandboxService(
      SandboxProperties props,
      AiSandboxRepository sandboxRepository,
      AiSandboxFileRepository fileRepository) {
    this.props = props;
    this.sandboxRepository = sandboxRepository;
    this.fileRepository = fileRepository;
  }

  @Transactional
  public AiSandbox open(Long userId, Long organisationId, Map<String, Object> options) {
    String folder = this.newFolder();
    for (String zone : ZONES) {
      this.zonePath(folder, zone).toFile().mkdirs();
    }

    AiSandbox sb = new AiSandbox();
    sb.setOrganisationId(organisationId);
    sb.setUserId(userId);
    sb.setConversationId(asLong(options.get("conversation_id")));
    sb.setName(asString(options.get("name")));
    sb.setPattern(options.getOrDefault("pattern", AiSandbox.PATTERN_STANDARD).toString());
    sb.setEngine(options.getOrDefault("engine", AiSandbox.ENGINE_LOCAL).toString());
    sb.setStatus(AiSandbox.STATUS_CREATED);
    sb.setFolder(folder);
    sb.setExpiresAt(LocalDateTime.now().plusHours(this.props.getTtlHours()));
    return this.sandboxRepository.save(sb);
  }

  @Transactional
  public AiSandboxFile write(
      AiSandbox sandbox, String section, String relativePath, String content) {
    this.assertZone(section, WRITE_ZONES);
    Path full = this.resolvePath(sandbox, section, relativePath);
    try {
      Files.createDirectories(full.getParent());
      Files.writeString(full, content, StandardCharsets.UTF_8);
    } catch (IOException e) {
      throw new UncheckedIOException(e);
    }
    return this.record(sandbox, section, full);
  }

  @Transactional
  public Map<String, Object> run(AiSandbox sandbox, String scriptPath) {
    sandbox.setStatus(AiSandbox.STATUS_RUNNING);
    this.sandboxRepository.save(sandbox);

    Path script = this.resolveReadPath(sandbox, scriptPath);
    List<String> command = List.of("python", script.toAbsolutePath().toString());
    ProcessBuilder pb = new ProcessBuilder(command);
    pb.directory(this.workspacePath(sandbox).toFile());
    pb.redirectErrorStream(false);

    int exitCode;
    String output;
    String error;
    try {
      Process p = pb.start();
      boolean finished = p.waitFor(this.props.getTimeoutSeconds(), TimeUnit.SECONDS);
      if (!finished) {
        p.destroyForcibly();
        exitCode = -1;
        output = "";
        error = "Timeout après " + this.props.getTimeoutSeconds() + "s.";
      } else {
        exitCode = p.exitValue();
        output = new String(p.getInputStream().readAllBytes(), StandardCharsets.UTF_8);
        error = new String(p.getErrorStream().readAllBytes(), StandardCharsets.UTF_8);
      }
    } catch (IOException | InterruptedException e) {
      exitCode = -1;
      output = "";
      error = e.getMessage();
    }

    String status = exitCode == 0 ? AiSandbox.STATUS_SUCCESS : AiSandbox.STATUS_ERROR;
    sandbox.setStatus(status);
    sandbox.setLastOutput((output + "\n" + error).trim());
    this.sandboxRepository.save(sandbox);
    this.recordLogs(sandbox, output, error);

    return Map.of(
        "exit_code", exitCode,
        "output", output,
        "error", error);
  }

  @Transactional
  public List<AiSandboxFile> close(AiSandbox sandbox) {
    List<AiSandboxFile> files = new ArrayList<>();
    Path outputDir = this.zonePath(sandbox.getFolder(), "output");
    if (Files.isDirectory(outputDir)) {
      try (var stream = Files.walk(outputDir)) {
        List<Path> paths = stream.filter(Files::isRegularFile).toList();
        for (Path file : paths) {
          files.add(this.record(sandbox, "output", file));
        }
      } catch (IOException e) {
        throw new UncheckedIOException(e);
      }
    }
    sandbox.setStatus(AiSandbox.STATUS_SUCCESS);
    this.sandboxRepository.save(sandbox);
    return files;
  }

  public Path workspacePath(AiSandbox sandbox) {
    return Paths.get(this.props.getRoot(), sandbox.getFolder());
  }

  public List<AiSandboxFile> outputs(AiSandbox sandbox) {
    return this.fileRepository.findBySandboxIdAndSection(sandbox.getId(), "output");
  }

  @Transactional
  public int purgeExpired() {
    List<AiSandbox> expired = this.sandboxRepository.findByExpiresAtBefore(LocalDateTime.now());
    for (AiSandbox sb : expired) {
      this.deleteWorkspace(sb);
      this.fileRepository.deleteAll(this.fileRepository.findBySandboxId(sb.getId()));
      this.sandboxRepository.delete(sb);
    }
    return expired.size();
  }

  public void deleteWorkspace(AiSandbox sandbox) {
    Path ws = this.workspacePath(sandbox);
    if (Files.exists(ws)) {
      this.deleteRecursively(ws.toFile());
    }
  }

  // ----------------------------------------------------------------------
  //  Helpers
  // ----------------------------------------------------------------------

  private String newFolder() {
    String folder;
    do {
      folder = "sb_" + Long.toHexString(this.random.nextLong() & 0xffffffffL) + Long.toHexString(this.random.nextInt(0xffff));
    } while (this.sandboxRepository.existsByFolder(folder));
    return folder;
  }

  private Path zonePath(String folder, String zone) {
    return Paths.get(this.props.getRoot(), folder, zone);
  }

  private void assertZone(String section, List<String> allowed) {
    if (!allowed.contains(section)) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Zone non autorisée : " + section + ".");
    }
  }

  private Path resolvePath(AiSandbox sandbox, String section, String relativePath) {
    Path zone = this.zonePath(sandbox.getFolder(), section).toAbsolutePath().normalize();
    String normalized = relativePath.replace('\\', '/');
    while (normalized.startsWith("/")) {
      normalized = normalized.substring(1);
    }
    if (normalized.isEmpty() || normalized.equals(".") || normalized.contains("..")) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Chemin de fichier invalide.");
    }
    if (normalized.startsWith("/") || normalized.matches("^[A-Za-z]:.*")) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Chemin absolu interdit.");
    }
    Path full = zone.resolve(normalized.replace('/', File.separatorChar)).normalize();
    if (!full.startsWith(zone)) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Chemin de fichier invalide (hors workspace).");
    }
    return full;
  }

  private Path resolveReadPath(AiSandbox sandbox, String scriptPath) {
    Path ws = this.workspacePath(sandbox).toAbsolutePath().normalize();
    String normalized = scriptPath.replace('\\', '/');
    if (normalized.isEmpty() || normalized.contains("..") || normalized.startsWith("/")) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Chemin de script invalide.");
    }
    Path full = ws.resolve(normalized.replace('/', File.separatorChar)).normalize();
    if (!full.startsWith(ws)) {
      throw new ApiException(HttpStatus.BAD_REQUEST, "Chemin de script invalide (hors workspace).");
    }
    if (!Files.isRegularFile(full)) {
      throw new ApiException(HttpStatus.NOT_FOUND, "Script introuvable : " + scriptPath + ".");
    }
    return full;
  }

  private AiSandboxFile record(AiSandbox sandbox, String section, Path fullPath) {
    AiSandboxFile f = new AiSandboxFile();
    f.setSandboxId(sandbox.getId());
    f.setSection(section);
    f.setPath(fullPath.toString());
    f.setName(fullPath.getFileName().toString());
    f.setSize(Files.exists(fullPath) ? safeSize(fullPath) : 0L);
    f.setMime(guessMime(fullPath));
    f.setHash(sha256(fullPath));
    return this.fileRepository.save(f);
  }

  private void recordLogs(AiSandbox sandbox, String output, String error) {
    String log = (output == null ? "" : output)
        + ((error != null && !error.isEmpty()) ? "\n" + error : "");
    if (log.isEmpty()) {
      return;
    }
    Path path = this.zonePath(sandbox.getFolder(), "logs").resolve("run.log");
    try {
      Files.writeString(path, log, StandardCharsets.UTF_8);
    } catch (IOException e) {
      throw new UncheckedIOException(e);
    }
    this.record(sandbox, "logs", path);
  }

  private static long safeSize(Path p) {
    try {
      return Files.size(p);
    } catch (IOException e) {
      return 0L;
    }
  }

  private static String guessMime(Path p) {
    String name = p.getFileName().toString().toLowerCase();
    if (name.endsWith(".pdf")) return "application/pdf";
    if (name.endsWith(".png")) return "image/png";
    if (name.endsWith(".jpg") || name.endsWith(".jpeg")) return "image/jpeg";
    if (name.endsWith(".xlsx")) return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    if (name.endsWith(".csv")) return "text/csv";
    if (name.endsWith(".txt") || name.endsWith(".log")) return "text/plain";
    if (name.endsWith(".py")) return "text/x-python";
    if (name.endsWith(".svg")) return "image/svg+xml";
    return "application/octet-stream";
  }

  private static String sha256(Path p) {
    try {
      MessageDigest md = MessageDigest.getInstance("SHA-256");
      byte[] hash = md.digest(Files.readAllBytes(p));
      return HexFormat.of().formatHex(hash);
    } catch (NoSuchAlgorithmException | IOException e) {
      return null;
    }
  }

  private void deleteRecursively(File f) {
    File[] children = f.listFiles();
    if (children != null) {
      for (File c : children) {
        this.deleteRecursively(c);
      }
    }
    f.delete();
  }

  private static Long asLong(Object v) {
    if (v == null) return null;
    if (v instanceof Number n) return n.longValue();
    try {
      return Long.parseLong(String.valueOf(v));
    } catch (NumberFormatException e) {
      return null;
    }
  }

  private static String asString(Object v) {
    return v == null ? null : String.valueOf(v);
  }
}
