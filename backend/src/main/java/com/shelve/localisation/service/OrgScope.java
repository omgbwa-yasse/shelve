package com.shelve.localisation.service;

import com.shelve.localisation.entity.Container;
import com.shelve.localisation.entity.Room;
import com.shelve.localisation.entity.Shelf;
import jakarta.persistence.criteria.Expression;
import jakarta.persistence.criteria.Join;
import jakarta.persistence.criteria.JoinType;
import jakarta.persistence.criteria.Predicate;
import java.io.Serializable;
import org.springframework.data.jpa.domain.Specification;

public final class OrgScope {
  private OrgScope() {}

  public static Specification<Room> roomsInOrganisation(Long organisationId) {
    return (Specification & Serializable)
        (root, query, cb) -> {
          query.distinct(true);
          Join join = root.join("organisations", JoinType.INNER);
          return cb.equal((Expression) join.get("id"), (Object) organisationId);
        };
  }

  public static Specification<Shelf> shelvesInOrganisation(Long organisationId) {
    return (Specification & Serializable)
        (root, query, cb) -> {
          query.distinct(true);
          Join room = root.join("room", JoinType.INNER);
          Join join = room.join("organisations", JoinType.INNER);
          return cb.equal((Expression) join.get("id"), (Object) organisationId);
        };
  }

  /**
   * Conteneurs de l'organisation courante.
   *
   * Un conteneur posé sur une étagère hérite de l'organisation via sa salle
   * (`shelf.room.organisations`). Un conteneur sans étagère (boîte de courrier
   * non localisée) retombe sur son `creator_organisation_id` — jamais sur le
   * créateur quand il est localisé (isolation R03 stricte côté notices).
   */
  public static Specification<Container> containersInOrganisation(Long organisationId) {
    return (Specification & Serializable)
        (root, query, cb) -> {
          query.distinct(true);
          Join shelf = root.join("shelf", JoinType.LEFT);
          Join room = shelf.join("room", JoinType.LEFT);
          Join org = room.join("organisations", JoinType.LEFT);
          Predicate viaRoom = cb.equal((Expression) org.get("id"), (Object) organisationId);
          Predicate viaCreator =
              cb.and(
                  cb.isNull(root.get("shelveId")),
                  cb.equal(
                      (Expression) root.get("creatorOrganisationId"), (Object) organisationId));
          return cb.or(viaRoom, viaCreator);
        };
  }
}
