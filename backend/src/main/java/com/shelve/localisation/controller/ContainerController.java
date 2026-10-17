package com.shelve.localisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.localisation.service.OrgScope;
import com.shelve.localisation.entity.Container;
import com.shelve.localisation.repository.ContainerRepository;
import com.shelve.localisation.repository.ShelfRepository;
import com.shelve.organisation.repository.OrganisationRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
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
@RequestMapping(value = {"/api/v1/containers"})
public class ContainerController {
  private static final String USAGE_RECORDS = "records";
  private static final String USAGE_MAIL = "mail";

  private static final List<String> FILTERABLE =
      List.of(
          "id",
          "code",
          "name",
          "usage",
          "shelve_id",
          "status_id",
          "property_id",
          "is_archived",
          "created_at",
          "updated_at");
  private static final List<String> SORTABLE =
      List.of(
          "id", "code", "name", "usage", "shelve_id", "status_id", "property_id", "created_at",
          "updated_at");
  private static final List<String> INCLUDABLE = List.of("shelf", "status", "property", "records");
  private final ContainerRepository repository;
  private final ShelfRepository shelfRepository;
  private final OrganisationRepository organisationRepository;

  public ContainerController(
      ContainerRepository repository,
      ShelfRepository shelfRepository,
      OrganisationRepository organisationRepository) {
    this.repository = repository;
    this.shelfRepository = shelfRepository;
    this.organisationRepository = organisationRepository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    if (request.getParameter("shelf_id") != null) {
      qp.getFilters()
          .computeIfAbsent("shelve_id", k -> new LinkedHashMap())
          .put("eq", request.getParameter("shelf_id"));
    }
    Specification<Container> orgScope =
        OrgScope.containersInOrganisation(auth.user().getCurrentOrganisationId());
    Specification spec = orgScope.and(Filters.of(qp.getFilters(), Container.class));
    return Paging.page(this.repository, spec, qp, SORTABLE, "id", request, this::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_view");
    Container container = this.findInOrganisation(id, auth);
    return Json.of("data", this.view(container));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_create");
    String code = ContainerController.str(body.get("code"));
    String usage = ContainerController.str(body.get("usage"));
    if (usage == null || (!USAGE_RECORDS.equals(usage) && !USAGE_MAIL.equals(usage))) {
      usage = USAGE_RECORDS;
    }
    Long shelveId = ContainerController.parseId(body.get("shelve_id"));
    Long statusId = ContainerController.parseId(body.get("status_id"));
    Long propertyId = ContainerController.parseId(body.get("property_id"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 50, "code");
    v.validate();
    if (shelveId != null
        && !this.shelfInOrganisation(shelveId, auth.user().getCurrentOrganisationId())) {
      throw ValidationException.single(
          "shelve_id", "Le rayonnage n'appartient pas \u00e0 votre organisation.");
    }
    Container container = new Container();
    container.setCode(this.codeWithOrgPrefix(code, usage, auth));
    container.setUsage(usage);
    if (body.containsKey("name")) {
      container.setName(ContainerController.str(body.get("name")));
    }
    container.setShelveId(shelveId);
    container.setStatusId(statusId);
    container.setPropertyId(propertyId);
    container.setCreatorId(auth.user().getId());
    container.setCreatorOrganisationId(auth.user().getCurrentOrganisationId());
    if (body.containsKey("is_archived")) {
      container.setIsArchived(ContainerController.bool(body.get("is_archived")));
    }
    this.repository.save(container);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/containers/" + container.getId()}))
        .body(Json.of("data", this.view(container)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_update");
    Container container = this.findInOrganisation(id, auth);
    if (body.containsKey("code")) {
      container.setCode(this.codeWithOrgPrefix(ContainerController.str(body.get("code")), container.getUsage(), auth));
    }
    if (body.containsKey("name")) {
      container.setName(ContainerController.str(body.get("name")));
    }
    if (body.containsKey("usage")) {
      String usage = ContainerController.str(body.get("usage"));
      if (USAGE_RECORDS.equals(usage) || USAGE_MAIL.equals(usage)) {
        container.setUsage(usage);
      }
    }
    if (body.containsKey("shelve_id")) {
      container.setShelveId(ContainerController.parseId(body.get("shelve_id")));
    }
    if (body.containsKey("status_id")) {
      container.setStatusId(ContainerController.parseId(body.get("status_id")));
    }
    if (body.containsKey("property_id")) {
      container.setPropertyId(ContainerController.parseId(body.get("property_id")));
    }
    if (body.containsKey("is_archived")) {
      container.setIsArchived(ContainerController.bool(body.get("is_archived")));
    }
    this.repository.save(container);
    return Json.of("data", this.view(container));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_delete");
    Container container = this.findInOrganisation(id, auth);
    this.repository.delete(container);
    return ResponseEntity.noContent().build();
  }

  private Container findInOrganisation(Long id, AuthenticatedUser auth) {
    return (Container)
        this.repository
            .findAll(
                OrgScope.containersInOrganisation(auth.user().getCurrentOrganisationId())
                    .and((root, q, cb) -> cb.equal(root.get("id"), id)))
            .stream()
            .findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private boolean shelfInOrganisation(Long shelfId, Long organisationId) {
    return this.shelfRepository
        .findAll(
            OrgScope.shelvesInOrganisation(organisationId)
                .and(
                    (Specification & Serializable)
                        (root, q, cb) -> cb.equal((Expression) root.get("id"), (Object) shelfId)))
        .stream()
        .findFirst()
        .isPresent();
  }

  /**
   * Préfixe le code d'une boîte de courrier (usage=mail) par le code de l'organisation
   * courante — ex. org « DG » + saisie « 001 » → « DG-001 ». Idempotent.
   */
  private String codeWithOrgPrefix(String code, String usage, AuthenticatedUser auth) {
    if (!USAGE_MAIL.equals(usage) || code == null) {
      return code;
    }
    Long orgId = auth.user().getCurrentOrganisationId();
    String orgCode =
        orgId != null
            ? this.organisationRepository.findById(orgId).map(org -> org.getCode()).orElse(null)
            : null;
    if (orgCode != null && !orgCode.isEmpty() && !code.startsWith(orgCode + "-")) {
      return orgCode + "-" + code;
    }
    return code;
  }

  private Map<String, Object> view(Container c) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", c.getId());
    map.put("code", c.getCode());
    map.put("name", c.getName());
    map.put("usage", c.getUsage());
    map.put("shelve_id", c.getShelveId());
    map.put("status_id", c.getStatusId());
    map.put("property_id", c.getPropertyId());
    map.put("creator_id", c.getCreatorId());
    map.put("creator_organisation_id", c.getCreatorOrganisationId());
    map.put("is_archived", c.getIsArchived() != null && c.getIsArchived() != false);
    map.put("created_at", Json.timestamp(c.getCreatedAt()));
    map.put("updated_at", Json.timestamp(c.getUpdatedAt()));
    return map;
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static boolean bool(Object value) {
    if (value == null) {
      return false;
    }
    if (value instanceof Boolean) {
      Boolean b = (Boolean) value;
      return b;
    }
    return Boolean.parseBoolean(String.valueOf(value));
  }

  private static Long parseId(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }
}
