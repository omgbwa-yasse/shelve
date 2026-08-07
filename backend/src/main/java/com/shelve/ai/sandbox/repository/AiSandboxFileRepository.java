package com.shelve.ai.sandbox.repository;

import com.shelve.ai.sandbox.entity.AiSandboxFile;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface AiSandboxFileRepository extends JpaRepository<AiSandboxFile, Long> {
  List<AiSandboxFile> findBySandboxId(Long sandboxId);

  List<AiSandboxFile> findBySandboxIdAndSection(Long sandboxId, String section);
}
