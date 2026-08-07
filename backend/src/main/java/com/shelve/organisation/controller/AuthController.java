package com.shelve.organisation.controller;

import com.shelve.exception.ValidationException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.organisation.service.PermissionService;
import com.shelve.organisation.service.TokenService;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.entity.UserOrganisationRoleId;
import com.shelve.organisation.repository.UserOrganisationRoleRepository;
import com.shelve.organisation.repository.UserRepository;
import com.shelve.organisation.repository.UserRoleRepository;
import com.shelve.organisation.mapper.UserViewMapper;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import org.springframework.http.HttpStatus;
import org.springframework.http.HttpStatusCode;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/auth"})
public class AuthController {
  private final UserRepository userRepository;
  private final OrganisationRepository organisationRepository;
  private final UserOrganisationRoleRepository pivotRepository;
  private final UserRoleRepository userRoleRepository;
  private final PasswordEncoder passwordEncoder;
  private final TokenService tokenService;
  private final PermissionService permissionService;

  public AuthController(
      UserRepository userRepository,
      OrganisationRepository organisationRepository,
      UserOrganisationRoleRepository pivotRepository,
      UserRoleRepository userRoleRepository,
      PasswordEncoder passwordEncoder,
      TokenService tokenService,
      PermissionService permissionService) {
    this.userRepository = userRepository;
    this.organisationRepository = organisationRepository;
    this.pivotRepository = pivotRepository;
    this.userRoleRepository = userRoleRepository;
    this.passwordEncoder = passwordEncoder;
    this.tokenService = tokenService;
    this.permissionService = permissionService;
  }

  @PostMapping(value = {"/login"})
  public ResponseEntity<Map<String, Object>> login(@RequestBody Map<String, Object> body) {
    LinkedHashMap<String, List<String>> errors = new LinkedHashMap<String, List<String>>();
    String email = AuthController.str(body.get("email"));
    String password = AuthController.str(body.get("password"));
    if (email == null || email.isBlank()) {
      errors.put("email", List.of("The email field is required."));
    } else if (!email.matches("^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$")) {
      errors.put("email", List.of("The email field must be a valid email address."));
    }
    if (password == null || password.isBlank()) {
      errors.put("password", List.of("The password field is required."));
    }
    if (!errors.isEmpty()) {
      throw new ValidationException(errors);
    }
    User user = this.userRepository.findByEmail(email).orElse(null);
    if (user == null
        || !this.passwordEncoder.matches((CharSequence) password, user.getPassword())) {
      String message = "These credentials do not match our records.";
      throw ValidationException.single("email", message);
    }
    String deviceName = AuthController.str(body.get("device_name"));
    String token =
        this.tokenService.createToken(
            user.getId(), deviceName != null && !deviceName.isBlank() ? deviceName : "api");
    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("token", token);
    data.put("token_type", "Bearer");
    data.put("user", this.userMap(user, true));
    data.put("permissions", this.permissionService.effectivePermissionNames(user.getId()));
    return ResponseEntity.ok(Map.of("data", data));
  }

  @GetMapping(value = {"/me"})
  public Map<String, Object> me() {
    AuthenticatedUser auth = CurrentUser.get();
    User user = auth.user();
    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("user", this.userMap(user, true));
    data.put("permissions", this.permissionService.effectivePermissionNames(user.getId()));
    return Map.of("data", data);
  }

  @PostMapping(value = {"/logout"})
  public ResponseEntity<Void> logout() {
    AuthenticatedUser auth = CurrentUser.get();
    this.tokenService.revoke(auth.tokenId());
    return ResponseEntity.noContent().build();
  }

  @PostMapping(value = {"/logout-all"})
  public ResponseEntity<Void> logoutAll() {
    AuthenticatedUser auth = CurrentUser.get();
    this.tokenService.revokeAllForUser(auth.user().getId());
    return ResponseEntity.noContent().build();
  }

  @PostMapping(value = {"/switch-organisation"})
  public ResponseEntity<Object> switchOrganisation(
      @RequestBody Map<String, Object> body, HttpServletRequest request) {
    Long organisationId;
    AuthenticatedUser auth = CurrentUser.get();
    User user = auth.user();
    Object rawId = body.get("organisation_id");
    if (rawId == null) {
      throw ValidationException.single("organisation_id", "The organisation id field is required.");
    }
    try {
      organisationId = ((Number) rawId).longValue();
    } catch (ClassCastException e) {
      throw ValidationException.single(
          "organisation_id", "The organisation id must be an integer.");
    }
    if (!this.organisationRepository.existsById(organisationId)) {
      throw ValidationException.single(
          "organisation_id", "The selected organisation id is invalid.");
    }
    boolean allowed =
        auth.isSuperAdmin()
            || this.pivotRepository.existsById(
                new UserOrganisationRoleId(user.getId(), organisationId));
    boolean bl = allowed;
    if (!allowed) {
      LinkedHashMap<String, Object> problem = new LinkedHashMap<String, Object>();
      problem.put("type", "https://shelve.local/errors/forbidden");
      problem.put("title", "Vous n'\u00eates pas rattach\u00e9 \u00e0 cette organisation.");
      problem.put("status", 403);
      problem.put("instance", request.getRequestURI());
      return ResponseEntity.status((HttpStatusCode) HttpStatus.FORBIDDEN)
          .contentType(MediaType.APPLICATION_PROBLEM_JSON)
          .body(problem);
    }
    user.setCurrentOrganisationId(organisationId);
    this.userRepository.save(user);
    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("user", this.userMap(user, true));
    data.put("permissions", this.permissionService.effectivePermissionNames(user.getId()));
    return ResponseEntity.ok(Map.of("data", data));
  }

  private Map<String, Object> userMap(User user, boolean withContext) {
    boolean isSuperadmin = this.permissionService.isSuperAdmin(user.getId());
    Organisation current =
        user.getCurrentOrganisationId() != null
            ? (Organisation)
                this.organisationRepository.findById(user.getCurrentOrganisationId()).orElse(null)
            : null;
    List<Organisation> organisations =
        this.permissionService.organisationIdsOf(user.getId()).stream()
            .map(arg_0 -> this.organisationRepository.findById(arg_0))
            .filter(Optional::isPresent)
            .map(Optional::get)
            .toList();
    List<String> roles = this.userRoleRepository.findRoleNamesByUserId(user.getId());
    return UserViewMapper.toMap(user, isSuperadmin, current, organisations, roles, withContext);
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
