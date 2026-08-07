package com.shelve.localisation.service;

import com.shelve.localisation.entity.Container;
import com.shelve.localisation.entity.Room;
import com.shelve.localisation.entity.Shelf;
import jakarta.persistence.criteria.Expression;
import jakarta.persistence.criteria.Join;
import jakarta.persistence.criteria.JoinType;
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

  public static Specification<Container> containersInOrganisation(Long organisationId) {
    return (Specification & Serializable)
        (root, query, cb) -> {
          query.distinct(true);
          Join shelf = root.join("shelf", JoinType.INNER);
          Join room = shelf.join("room", JoinType.INNER);
          Join join = room.join("organisations", JoinType.INNER);
          return cb.equal((Expression) join.get("id"), (Object) organisationId);
        };
  }
}
