package com.shelve.security;

import com.shelve.organisation.service.PermissionService;
import com.shelve.organisation.entity.PersonalAccessToken;
import com.shelve.organisation.repository.PersonalAccessTokenRepository;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.repository.UserRepository;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.HexFormat;
import java.util.List;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

@Component
public class SanctumTokenFilter extends OncePerRequestFilter {
  private static final String TOKENABLE_TYPE = "App\\Models\\User";
  private final PersonalAccessTokenRepository tokenRepository;
  private final UserRepository userRepository;
  private final PermissionService permissionService;

  public SanctumTokenFilter(
      PersonalAccessTokenRepository tokenRepository,
      UserRepository userRepository,
      PermissionService permissionService) {
    this.tokenRepository = tokenRepository;
    this.userRepository = userRepository;
    this.permissionService = permissionService;
  }

  protected void doFilterInternal(
      HttpServletRequest request, HttpServletResponse response, FilterChain filterChain)
      throws ServletException, IOException {
    String bearer;
    AuthenticatedUser auth;
    String header = request.getHeader("Authorization");
    if (header != null
        && header.startsWith("Bearer ")
        && (auth = this.authenticate(bearer = header.substring(7).trim())) != null) {
      UsernamePasswordAuthenticationToken token =
          new UsernamePasswordAuthenticationToken(
              (Object) auth, null, List.of(new SimpleGrantedAuthority("ROLE_AGENT")));
      SecurityContextHolder.getContext().setAuthentication((Authentication) token);
    }
    filterChain.doFilter((ServletRequest) request, (ServletResponse) response);
  }

  private AuthenticatedUser authenticate(String bearer) {
    long tokenId;
    int pipe = bearer.indexOf(124);
    if (pipe <= 0) {
      return null;
    }
    try {
      tokenId = Long.parseLong(bearer.substring(0, pipe));
    } catch (NumberFormatException e) {
      return null;
    }
    String plain = bearer.substring(pipe + 1);
    if (plain.isEmpty()) {
      return null;
    }
    PersonalAccessToken token = this.tokenRepository.findById(tokenId).orElse(null);
    if (token == null
        || !TOKENABLE_TYPE.equals(token.getTokenableType())
        || !SanctumTokenFilter.constantTimeEquals(
            token.getToken(), SanctumTokenFilter.sha256(plain))) {
      return null;
    }
    User user = this.userRepository.findById(token.getTokenableId()).orElse(null);
    if (user == null) {
      return null;
    }
    List<String> permissions =
        this.permissionService.effectivePermissionNamesInOrganisation(
            user.getId(), user.getCurrentOrganisationId());
    boolean isSuperadmin = this.permissionService.isSuperAdmin(user.getId());
    return new AuthenticatedUser(user, permissions, tokenId, isSuperadmin);
  }

  private static String sha256(String value) {
    try {
      MessageDigest digest = MessageDigest.getInstance("SHA-256");
      byte[] hash = digest.digest(value.getBytes(StandardCharsets.UTF_8));
      return HexFormat.of().formatHex(hash);
    } catch (NoSuchAlgorithmException e) {
      throw new IllegalStateException("SHA-256 indisponible", e);
    }
  }

  private static boolean constantTimeEquals(String a, String b) {
    return MessageDigest.isEqual(
        a.getBytes(StandardCharsets.UTF_8), b.getBytes(StandardCharsets.UTF_8));
  }
}
