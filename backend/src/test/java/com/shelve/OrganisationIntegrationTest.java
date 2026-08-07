/*
 * Decompiled with CFR 0.152.
 *
 * Could not load the following classes:
 *  io.restassured.response.Response
 *  io.restassured.response.ValidatableResponse
 *  org.assertj.core.api.Assertions
 *  org.hamcrest.Matcher
 *  org.hamcrest.Matchers
 *  org.junit.jupiter.api.Test
 */
package com.shelve;

import io.restassured.response.Response;
import io.restassured.response.ValidatableResponse;
import java.util.concurrent.ThreadLocalRandom;
import org.assertj.core.api.Assertions;
import org.hamcrest.Matcher;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Test;

class OrganisationIntegrationTest extends AbstractIntegrationTest {
  OrganisationIntegrationTest() {}

  private int suffix() {
    return ThreadLocalRandom.current().nextInt(10000, 99999);
  }

  @Test
  void organisations_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.authed()
                                            .contentType("application/json")
                                            .body(
                                                "{\"code\":\"ORG"
                                                    + suffix
                                                    + "\",\"name\":\"Organisation "
                                                    + suffix
                                                    + "\"}")
                                            .post("/api/v1/organisations", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body(
                        "data.name", Matchers.startsWith((String) "Organisation "), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/organisations/" + id, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((Response)
                                    this.authed()
                                        .contentType("application/json")
                                        .body("{}")
                                        .post("/api/v1/organisations", new Object[0]))
                                .then())
                        .statusCode(422))
                .body("errors.code", Matchers.notNullValue(), new Object[0]))
        .body("errors.name", Matchers.notNullValue(), new Object[0]);
  }

  @Test
  void roles_creeAvecGuardParDefaut() {
    int suffix = this.suffix();
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .contentType("application/json")
                                .body(
                                    "{\"name\":\"role-"
                                        + suffix
                                        + "\",\"description\":\"R\u00f4le de conformit\u00e9\"}")
                                .post("/api/v1/roles", new Object[0]))
                        .then())
                .statusCode(201))
        .body("data.guard_name", Matchers.equalTo((Object) "web"), new Object[0]);
  }

  @Test
  void users_neJamaisExposerDeSecret() {
    int suffix = this.suffix();
    int userId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.authed()
                                            .contentType("application/json")
                                            .body(
                                                "{\"name\":\"Agent\",\"surname\":\"Conformit\u00e9\",\"email\":\"agent-"
                                                    + suffix
                                                    + "@conformance.test\",\"birthday\":\"1990-01-01\",\"password\":\"mot-de-passe-secret\"}")
                                            .post("/api/v1/users", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body(
                        "data.email",
                        Matchers.endsWith((String) "@conformance.test"),
                        new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    String body =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((ValidatableResponse)
                                        ((Response)
                                                this.authed()
                                                    .get("/api/v1/users/" + userId, new Object[0]))
                                            .then())
                                    .statusCode(200))
                            .body("data.password", Matchers.nullValue(), new Object[0]))
                    .body("data.remember_token", Matchers.nullValue(), new Object[0]))
            .extract()
            .asString();
    Assertions.assertThat((String) body).doesNotContain(new CharSequence[] {"mot-de-passe-secret"});
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/users/" + userId, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void pivotOrganisation_refuseUneOrganisationEtrangere() {
    int suffix = this.suffix();
    int orgId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.authed()
                                    .contentType("application/json")
                                    .body(
                                        "{\"code\":\"O"
                                            + suffix
                                            + "\",\"name\":\"Organisation cible\"}")
                                    .post("/api/v1/organisations", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .contentType("application/json")
                        .body("{\"user_id\":1,\"organisation_id\":" + orgId + ",\"role_id\":1}")
                        .post("/api/v1/user-organisation-roles", new Object[0]))
                .then())
        .statusCode(
            (Matcher)
                Matchers.anyOf(
                    (Matcher) Matchers.is((Object) 403),
                    (Matcher) Matchers.is((Object) 404),
                    (Matcher) Matchers.is((Object) 422)));
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/organisations/" + orgId, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void pivotInexistant_repondSansDivulguer() {
    ((ValidatableResponse)
            ((Response)
                    this.authed()
                        .get("/api/v1/user-organisation-roles/999999/999999", new Object[0]))
                .then())
        .statusCode(
            (Matcher)
                Matchers.anyOf(
                    (Matcher) Matchers.is((Object) 403), (Matcher) Matchers.is((Object) 404)));
  }
}
