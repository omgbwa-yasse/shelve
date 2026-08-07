/*
 * Decompiled with CFR 0.152.
 *
 * Could not load the following classes:
 *  io.restassured.response.Response
 *  io.restassured.response.ValidatableResponse
 *  org.hamcrest.Matchers
 *  org.junit.jupiter.api.Test
 */
package com.shelve;

import io.restassured.response.Response;
import io.restassured.response.ValidatableResponse;
import java.util.concurrent.ThreadLocalRandom;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Test;

class LocalisationIntegrationTest extends AbstractIntegrationTest {
  LocalisationIntegrationTest() {}

  private int suffix() {
    return ThreadLocalRandom.current().nextInt(10000, 99999);
  }

  @Test
  void buildings_referentielGlobal() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.authed()
                                            .contentType("application/json")
                                            .body(
                                                "{\"name\":\"Site "
                                                    + suffix
                                                    + "\",\"visibility\":\"public\"}")
                                            .post("/api/v1/buildings", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.is_public", Matchers.is((Object) true), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/buildings/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void buildings_valideSesEntrees() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType("application/json")
                                .body("{\"name\":\"X\",\"visibility\":\"inconnu\"}")
                                .post("/api/v1/buildings", new Object[0]))
                        .then())
                .statusCode(422))
        .body("errors.visibility", Matchers.notNullValue(), new Object[0]);
  }

  @Test
  void chaineLocalisation_orgScopeEtChampsCalcules() {
    int suffix = this.suffix();
    int buildingId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"name\":\"Site "
                                            + suffix
                                            + "\",\"visibility\":\"public\"}")
                                    .post("/api/v1/buildings", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int floorId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"name\":\"RDC "
                                            + suffix
                                            + "\",\"building_id\":"
                                            + buildingId
                                            + "}")
                                    .post("/api/v1/floors", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int roomId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"code\":\"R"
                                            + suffix % 100000
                                            + "\",\"name\":\"Salle "
                                            + suffix
                                            + "\",\"visibility\":\"public\",\"type\":\"archives\",\"floor_id\":"
                                            + floorId
                                            + "}")
                                    .post("/api/v1/rooms", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int shelfId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"code\":\"S"
                                            + suffix % 100000
                                            + "\",\"face\":2,\"ear\":2,\"shelf\":3,\"shelf_length\":100,\"room_id\":"
                                            + roomId
                                            + "}")
                                    .post("/api/v1/shelves", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((ValidatableResponse)
                                    ((ValidatableResponse)
                                            ((ValidatableResponse)
                                                    ((Response)
                                                            this.authed()
                                                                .get(
                                                                    "/api/v1/shelves/" + shelfId,
                                                                    new Object[0]))
                                                        .then())
                                                .statusCode(200))
                                        .body(
                                            "data.total_capacity",
                                            Matchers.equalTo((Object) Float.valueOf(12.0f)),
                                            new Object[0]))
                                .body(
                                    "data.occupied_spots", Matchers.notNullValue(), new Object[0]))
                        .body("data.available_spots", Matchers.notNullValue(), new Object[0]))
                .body("data.occupancy_percentage", Matchers.notNullValue(), new Object[0]))
        .body("data.volumetry_ml", Matchers.notNullValue(), new Object[0]);
    int statusId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body("{\"name\":\"Statut " + suffix + "\"}")
                                    .post("/api/v1/container-statuses", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int propertyId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"name\":\"Type "
                                            + suffix
                                            + "\",\"width\":10,\"length\":20,\"depth\":30}")
                                    .post("/api/v1/container-properties", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int containerId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"code\":\"C"
                                            + suffix % 100000
                                            + "\",\"shelve_id\":"
                                            + shelfId
                                            + ",\"status_id\":"
                                            + statusId
                                            + ",\"property_id\":"
                                            + propertyId
                                            + "}")
                                    .post("/api/v1/containers", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/containers/" + containerId, new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/shelves/" + shelfId, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/rooms/" + roomId, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/floors/" + floorId, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/buildings/" + buildingId, new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response)
                    this.authed().delete("/api/v1/container-statuses/" + statusId, new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .delete("/api/v1/container-properties/" + propertyId, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void conteneurHorsOrganisation_repond404() {
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/containers/999999999", new Object[0])).then())
        .statusCode(404);
  }
}
