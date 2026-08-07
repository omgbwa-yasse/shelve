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
import java.util.List;
import java.util.concurrent.ThreadLocalRandom;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Test;

class ReferentialsIntegrationTest extends AbstractIntegrationTest {
  ReferentialsIntegrationTest() {}

  private int suffix() {
    return ThreadLocalRandom.current().nextInt(10000, 99999);
  }

  @Test
  void activities_index_estUneCollectionPaginee() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((ValidatableResponse)
                                    ((ValidatableResponse)
                                            ((ValidatableResponse)
                                                    ((Response)
                                                            this.authed()
                                                                .get(
                                                                    "/api/v1/activities",
                                                                    new Object[0]))
                                                        .then())
                                                .statusCode(200))
                                        .body("data", Matchers.isA(List.class), new Object[0]))
                                .body("meta.total", Matchers.notNullValue(), new Object[0]))
                        .body("meta.per_page", Matchers.equalTo((Object) 25), new Object[0]))
                .body("links", Matchers.notNullValue(), new Object[0]))
        .body("links.first", Matchers.notNullValue(), new Object[0]);
  }

  @Test
  void activities_index_refuseUnFiltreHorsListeBlanche() {
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .queryParam("filter[nom]", new Object[] {"x"})
                        .get("/api/v1/activities", new Object[0]))
                .then())
        .statusCode(400);
  }

  @Test
  void activities_crudComplet() {
    int code = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((ValidatableResponse)
                                        ((Response)
                                                this.authed()
                                                    .contentType("application/json")
                                                    .body(
                                                        "{\"code\":\"CONF"
                                                            + code
                                                            + "\",\"name\":\"Activit\u00e9 de"
                                                            + " conformit\u00e9\"}")
                                                    .post("/api/v1/activities", new Object[0]))
                                            .then())
                                    .statusCode(201))
                            .body("data.code", Matchers.startsWith((String) "CONF"), new Object[0]))
                    .body("data.id", Matchers.isA(Number.class), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((Response)
                                    this.authed().get("/api/v1/activities/" + id, new Object[0]))
                                .then())
                        .statusCode(200))
                .body("data.id", Matchers.equalTo((Object) id), new Object[0]))
        .body(
            "data.name",
            Matchers.equalTo((Object) "Activit\u00e9 de conformit\u00e9"),
            new Object[0]);
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType("application/json")
                                .body(
                                    "{\"name\":\"Activit\u00e9 de conformit\u00e9"
                                        + " (renomm\u00e9e)\"}")
                                .patch("/api/v1/activities/" + id, new Object[0]))
                        .then())
                .statusCode(200))
        .body(
            "data.name",
            Matchers.equalTo((Object) "Activit\u00e9 de conformit\u00e9 (renomm\u00e9e)"),
            new Object[0]);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/activities/" + id, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/activities/" + id, new Object[0])).then())
        .statusCode(404);
  }

  @Test
  void activities_store_valideSesEntrees() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((Response)
                                    this.authed()
                                        .contentType("application/json")
                                        .body("{}")
                                        .post("/api/v1/activities", new Object[0]))
                                .then())
                        .statusCode(422))
                .body("errors.code", Matchers.notNullValue(), new Object[0]))
        .body("errors.name", Matchers.notNullValue(), new Object[0]);
  }

  @Test
  void keywords_search_autocompletion() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .queryParam("q", new Object[] {"arch"})
                                .get("/api/v1/keywords/search", new Object[0]))
                        .then())
                .statusCode(200))
        .body("data", Matchers.isA(List.class), new Object[0]);
  }

  @Test
  void referenceLists_crudAvecValeurs() {
    int suffix = this.suffix();
    int listId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"name\":\"Civilit\u00e9s "
                                            + suffix
                                            + "\",\"code\":\"CIV"
                                            + suffix
                                            + "\"}")
                                    .post("/api/v1/reference-lists", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int valueId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body("{\"value\":\"M.\",\"code\":\"M\"}")
                                    .post(
                                        "/api/v1/reference-lists/" + listId + "/values",
                                        new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed().get("/api/v1/reference-lists/" + listId, new Object[0]))
                        .then())
                .statusCode(200))
        .body("data.values_count", Matchers.equalTo((Object) 1), new Object[0]);
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType("application/json")
                                .body("{\"value\":\"Mme\",\"code\":\"M\"}")
                                .post(
                                    "/api/v1/reference-lists/" + listId + "/values", new Object[0]))
                        .then())
                .statusCode(422))
        .body("errors.code", Matchers.notNullValue(), new Object[0]);
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType("application/json")
                                .body("{\"value\":\"Monsieur\"}")
                                .patch(
                                    "/api/v1/reference-lists/" + listId + "/values/" + valueId,
                                    new Object[0]))
                        .then())
                .statusCode(200))
        .body("data.value", Matchers.equalTo((Object) "Monsieur"), new Object[0]);
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .delete(
                            "/api/v1/reference-lists/" + listId + "/values/" + valueId,
                            new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/reference-lists/" + listId, new Object[0]))
                .then())
        .statusCode(204);
  }
}
