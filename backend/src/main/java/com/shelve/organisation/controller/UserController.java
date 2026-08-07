package com.shelve.organisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.organisation.entity.User;
import com.shelve.organisation.repository.UserRepository;
import com.shelve.organisation.repository.UserRoleRepository;
import com.shelve.organisation.mapper.UserViewMapper;
import jakarta.servlet.http.HttpServletRequest;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@Transactional
@RestController
@RequestMapping(value = {"/api/v1/users"})
public class UserController {
  private static final List<String> FILTERABLE =
      List.of(
          "id",
          "name",
          "surname",
          "birthday",
          "email",
          "email_verified_at",
          "current_organisation_id",
          "created_at",
          "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("organisation", "organisations", "roles");
  private final UserRepository repository;
  private final OrganisationRepository organisationRepository;
  private final UserRoleRepository userRoleRepository;
  private final PasswordEncoder passwordEncoder;

  public UserController(
      UserRepository repository,
      OrganisationRepository organisationRepository,
      UserRoleRepository userRoleRepository,
      PasswordEncoder passwordEncoder) {
    this.repository = repository;
    this.organisationRepository = organisationRepository;
    this.userRoleRepository = userRoleRepository;
    this.passwordEncoder = passwordEncoder;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "users_view");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), User.class),
        qp,
        SORTABLE,
        "id",
        request,
        this::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "users_view");
    User user = (User) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", this.view(user));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "users_create");
    String name = UserController.str(body.get("name"));
    String surname = UserController.str(body.get("surname"));
    String email = UserController.str(body.get("email"));
    String birthday = UserController.str(body.get("birthday"));
    String password = UserController.str(body.get("password"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 191, "name")
            .require("email", email, "The email field is required.")
            .max("email", email, 191, "email")
            .require("birthday", birthday, "The birthday field is required.")
            .require("password", password, "The password field is required.")
            .max("password", password, 255, "password");
    if (email != null && !email.matches("^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$")) {
      v.add("email", "The email field must be a valid email address.");
    }
    if (email != null && this.repository.findByEmail(email).isPresent()) {
      v.add("email", "The email has already been taken.");
    }
    if (password != null && password.length() < 8) {
      v.add("password", "The password field must be at least 8 characters.");
    }
    v.validate();
    User user = new User();
    user.setName(name);
    user.setSurname(surname);
    user.setEmail(email);
    user.setBirthday(UserController.parseBirthday(birthday, v));
    user.setPassword(this.passwordEncoder.encode((CharSequence) password));
    this.repository.save(user);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/users/" + user.getId()}))
        .body(Json.of("data", this.view(user)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "users_update");
    User user = (User) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      user.setName(UserController.str(body.get("name")));
    }
    if (body.containsKey("surname")) {
      user.setSurname(UserController.str(body.get("surname")));
    }
    if (body.containsKey("email")) {
      user.setEmail(UserController.str(body.get("email")));
    }
    if (body.containsKey("birthday")) {
      user.setBirthday(
          UserController.parseBirthday(UserController.str(body.get("birthday")), null));
    }
    if (body.containsKey("password")) {
      user.setPassword(
          this.passwordEncoder.encode((CharSequence) UserController.str(body.get("password"))));
    }
    this.repository.save(user);
    return Json.of("data", this.view(user));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "users_delete");
    User user = (User) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(user);
    return ResponseEntity.noContent().build();
  }

  private Map<String, Object> view(User user) {
    return UserViewMapper.toMap(
        user,
        this.authIsSuperAdmin(user),
        user.getCurrentOrganisationId() != null
            ? (Organisation)
                this.organisationRepository.findById(user.getCurrentOrganisationId()).orElse(null)
            : null,
        user.getCurrentOrganisationId() != null
                && this.organisationRepository.findById(user.getCurrentOrganisationId()).isPresent()
            ? List.of(
                (Organisation)
                    this.organisationRepository.findById(user.getCurrentOrganisationId()).get())
            : List.of(),
        this.userRoleRepository.findRoleNamesByUserId(user.getId()),
        false);
  }

  private boolean authIsSuperAdmin(User user) {
    return this.userRoleRepository.findRoleNamesByUserId(user.getId()).contains("superadmin");
  }

  private static LocalDate parseBirthday(String value, Validator validator) {
    try {
      return LocalDate.parse(value);
    } catch (DateTimeParseException e) {
      if (validator != null) {
        validator.add("birthday", "The birthday field must be a valid date.");
        validator.validate();
      }
      return null;
    }
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
