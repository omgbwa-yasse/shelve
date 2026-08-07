/*
 * Decompiled with CFR 0.152.
 *
 * Could not load the following classes:
 *  io.restassured.RestAssured
 *  io.restassured.http.ContentType
 *  io.restassured.response.Response
 *  io.restassured.response.ValidatableResponse
 *  org.assertj.core.api.Assertions
 *  org.hamcrest.Matchers
 *  org.junit.jupiter.api.Test
 */
package com.shelve;

import io.restassured.RestAssured;
import io.restassured.http.ContentType;
import io.restassured.response.Response;
import io.restassured.response.ValidatableResponse;
import org.assertj.core.api.Assertions;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Test;

class AuthIntegrationTest extends AbstractIntegrationTest {
  AuthIntegrationTest() {}

  @Test
  void login_renvoieTokenProfilEtPermissions() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((ValidatableResponse)
                                    ((ValidatableResponse)
                                            ((ValidatableResponse) this.login().then())
                                                .statusCode(200))
                                        .body("data.token", Matchers.notNullValue(), new Object[0]))
                                .body(
                                    "data.token_type",
                                    Matchers.equalTo((Object) "Bearer"),
                                    new Object[0]))
                        .body(
                            "data.user.email",
                            Matchers.equalTo((Object) "conformance@shelve.test"),
                            new Object[0]))
                .body(
                    "data.permissions",
                    Matchers.hasItem((Object) "activity_create"),
                    new Object[0]))
        .body("data.user.is_superadmin", Matchers.is((Object) false), new Object[0]);
  }

  @Test
  void login_netoileJamaisLeMotDePasse() {
    String body =
        ((ValidatableResponse) ((ValidatableResponse) this.login().then()).statusCode(200))
            .extract()
            .asString();
    Assertions.assertThat((String) body).doesNotContain(new CharSequence[] {"password"});
  }

  @Test
  void login_refuseUnMotDePasseErrone() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            RestAssured.given()
                                .contentType(ContentType.JSON)
                                .body(
                                    "{\"email\":\"conformance@shelve.test\",\"password\":\"mauvais\"}")
                                .post("/api/v1/auth/login", new Object[0]))
                        .then())
                .statusCode(422))
        .body("errors.email", Matchers.notNullValue(), new Object[0]);
  }

  @Test
  void me_renvoieLeProfil() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((Response) this.authed().get("/api/v1/auth/me", new Object[0])).then())
                        .statusCode(200))
                .body(
                    "data.user.email",
                    Matchers.equalTo((Object) "conformance@shelve.test"),
                    new Object[0]))
        .body("data.permissions", Matchers.hasItem((Object) "building_viewAny"), new Object[0]);
  }

  @Test
  void me_exigeUnToken() {
    ((ValidatableResponse)
            ((Response)
                    RestAssured.given()
                        .accept(ContentType.JSON)
                        .get("/api/v1/auth/me", new Object[0]))
                .then())
        .statusCode(401);
  }

  @Test
  void logout_revoqueLeToken() {
    String token = this.token();
    ((ValidatableResponse)
            ((Response)
                    RestAssured.given()
                        .header("Authorization", (Object) ("Bearer " + token), new Object[0])
                        .post("/api/v1/auth/logout", new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response)
                    RestAssured.given()
                        .accept(ContentType.JSON)
                        .header("Authorization", (Object) ("Bearer " + token), new Object[0])
                        .get("/api/v1/auth/me", new Object[0]))
                .then())
        .statusCode(401);
  }

  @Test
  void switchOrganisation_refuseUneOrganisationEtrangere() {
    int orgId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType(ContentType.JSON)
                                    .body(
                                        "{\"code\":\"FGN"
                                            + System.nanoTime() % 100000L
                                            + "\",\"name\":\"Organisation \u00e9trang\u00e8re\"}")
                                    .post("/api/v1/organisations", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .contentType(ContentType.JSON)
                        .body("{\"organisation_id\":" + orgId + "}")
                        .post("/api/v1/auth/switch-organisation", new Object[0]))
                .then())
        .statusCode(403);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/organisations/" + orgId, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void switchOrganisation_valideSesEntrees() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType(ContentType.JSON)
                                .body("{\"organisation_id\":999999999}")
                                .post("/api/v1/auth/switch-organisation", new Object[0]))
                        .then())
                .statusCode(422))
        .body("errors.organisation_id", Matchers.notNullValue(), new Object[0]);
  }
}
