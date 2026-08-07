package com.shelve.localisation.entity;

import com.shelve.organisation.entity.Organisation;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.JoinTable;
import jakarta.persistence.ManyToMany;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.time.Instant;
import java.util.HashSet;
import java.util.Set;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "rooms")
public class Room {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, length = 10)
  private String code;

  @Column(nullable = false, length = 100)
  private String name;

  @Column(columnDefinition = "text")
  private String description;

  @Column(nullable = false, length = 20)
  private String visibility;

  @Column(nullable = false, length = 20)
  private String type;

  @Column(name = "floor_id", nullable = false)
  private Long floorId;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  @ManyToOne(fetch = FetchType.LAZY)
  @JoinColumn(name = "floor_id", insertable = false, updatable = false)
  private Floor floor;

  @ManyToMany(fetch = FetchType.LAZY)
  @JoinTable(
      name = "organisation_room",
      joinColumns = {@JoinColumn(name = "room_id")},
      inverseJoinColumns = {@JoinColumn(name = "organisation_id")})
  private Set<Organisation> organisations = new HashSet<Organisation>();

  public Set<Organisation> getOrganisations() {
    return this.organisations;
  }

  public void setOrganisations(Set<Organisation> organisations) {
    this.organisations = organisations;
  }

  public Floor getFloor() {
    return this.floor;
  }

  public String getEffectiveVisibility() {
    if ("inherit".equals(this.visibility)
        && this.floor != null
        && this.floor.getBuilding() != null) {
      return this.floor.getBuilding().getVisibility();
    }
    return this.visibility;
  }

  public Long getId() {
    return this.id;
  }

  public void setId(Long id) {
    this.id = id;
  }

  public String getCode() {
    return this.code;
  }

  public void setCode(String code) {
    this.code = code;
  }

  public String getName() {
    return this.name;
  }

  public void setName(String name) {
    this.name = name;
  }

  public String getDescription() {
    return this.description;
  }

  public void setDescription(String description) {
    this.description = description;
  }

  public String getVisibility() {
    return this.visibility;
  }

  public void setVisibility(String visibility) {
    this.visibility = visibility;
  }

  public String getType() {
    return this.type;
  }

  public void setType(String type) {
    this.type = type;
  }

  public Long getFloorId() {
    return this.floorId;
  }

  public void setFloorId(Long floorId) {
    this.floorId = floorId;
  }

  public Long getCreatorId() {
    return this.creatorId;
  }

  public void setCreatorId(Long creatorId) {
    this.creatorId = creatorId;
  }

  public Instant getCreatedAt() {
    return this.createdAt;
  }

  public Instant getUpdatedAt() {
    return this.updatedAt;
  }
}
