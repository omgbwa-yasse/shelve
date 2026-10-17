package com.shelve;

import io.restassured.RestAssured;
import io.restassured.http.ContentType;
import io.restassured.response.Response;
import io.restassured.response.ValidatableResponse;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.Test;

/**
 * Parité Spring de `MailController::externalCapabilities` (Laravel) — l'endpoint
 * expose les capacités courrier externe de l'organisation courante, consommé par
 * Next.js pour masquer les boutons « Envoyer (externe) » / « Recevoir (externe) ».
 */
class MailCapabilitiesIntegrationTest extends AbstractIntegrationTest {
  MailCapabilitiesIntegrationTest() {}

  @Test
  void externalCapabilities_requiresAuthentication() {
    ((ValidatableResponse)
            ((Response)
                    RestAssured.given()
                        .port(this.port)
                        .accept(ContentType.JSON)
                        .get("/api/v1/mails/external-capabilities", new Object[0]))
                .then())
        .statusCode(401);
  }

  @Test
  void externalCapabilities_returnsBooleansForCurrentOrganisation() {
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((ValidatableResponse)
                            ((ValidatableResponse)
                                    ((Response)
                                            this.authed()
                                                .get("/api/v1/mails/external-capabilities", new Object[0]))
                                        .then())
                                .statusCode(200))
                        .body(
                            "data.external_send_enabled",
                            Matchers.notNullValue(),
                            new Object[0]))
                .body(
                    "data.external_receive_enabled",
                    Matchers.notNullValue(),
                    new Object[0]))
        .body("data.external_send_enabled", Matchers.isA(Boolean.class), new Object[0])
        .body("data.external_receive_enabled", Matchers.isA(Boolean.class), new Object[0]);
  }
}
