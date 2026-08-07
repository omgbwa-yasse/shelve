package com.shelve.localisation.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.time.Instant;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

@Entity
@Table(name = "shelves")
public class Shelf {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(nullable = false, length = 30)
  private String code;

  @Column(columnDefinition = "longtext")
  private String observation;

  @Column(nullable = false)
  private Double face;

  @Column(nullable = false)
  private Double ear;

  @Column(nullable = false)
  private Double shelf;

  @Column(name = "shelf_length", nullable = false)
  private Double shelfLength;

  @Column(name = "room_id", nullable = false)
  private Long roomId;

  @Column(name = "creator_id", nullable = false)
  private Long creatorId;

  @CreationTimestamp
  @Column(name = "created_at", updatable = false)
  private Instant createdAt;

  @UpdateTimestamp
  @Column(name = "updated_at")
  private Instant updatedAt;

  @ManyToOne(fetch = FetchType.LAZY)
  @JoinColumn(name = "room_id", insertable = false, updatable = false)
  private Room room;

  public Room getRoom() {
    return this.room;
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

  public String getObservation() {
    return this.observation;
  }

  public void setObservation(String observation) {
    this.observation = observation;
  }

  public Double getFace() {
    return this.face;
  }

  public void setFace(Double face) {
    this.face = face;
  }

  public Double getEar() {
    return this.ear;
  }

  public void setEar(Double ear) {
    this.ear = ear;
  }

  public Double getShelf() {
    return this.shelf;
  }

  public void setShelf(Double shelf) {
    this.shelf = shelf;
  }

  public Double getShelfLength() {
    return this.shelfLength;
  }

  public void setShelfLength(Double shelfLength) {
    this.shelfLength = shelfLength;
  }

  public Long getRoomId() {
    return this.roomId;
  }

  public void setRoomId(Long roomId) {
    this.roomId = roomId;
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
