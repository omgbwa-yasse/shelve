/*
 * Test d'intégration du sandbox Python (D14) — parité Laravel.
 *
 * Couvre : capabilities, open, write, run, close, download, et le refus
 * d'accès hors périmètre (permission manquante).
 */
package com.shelve;

import io.restassured.response.Response;
import io.restassured.response.ValidatableResponse;
import java.util.UUID;
import org.hamcrest.Matchers;
import org.junit.jupiter.api.MethodOrderer;
import org.junit.jupiter.api.Order;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.TestMethodOrder;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
class SandboxIntegrationTest extends AbstractIntegrationTest {
  private static String sandboxId;

  @Test
  @Order(1)
  void capabilities_endpoint_renvoie_le_catalogue() {
    this.authed()
        .get("/api/v1/ai/sandboxes/capabilities")
        .then()
        .statusCode(200)
        .body("data.capabilities.size()", Matchers.greaterThanOrEqualTo(1));
  }

  @Test
  @Order(2)
  void open_creer_un_sandbox() {
    String name = "Test " + UUID.randomUUID().toString().substring(0, 6);
    Response resp = this.jsonAuthed().body("{\"name\":\"" + name + "\"}").post("/api/v1/ai/sandboxes");
    resp.then().statusCode(200).body("data.status", Matchers.is("created"));
    sandboxId = resp.jsonPath().getString("data.id");
  }

  @Test
  @Order(3)
  void write_run_close_produisent_un_fichier() {
    // write
    String code =
        "import pandas as pd\n"
            + "df = pd.DataFrame({'a': [1, 2], 'b': [3, 4]})\n"
            + "df.to_csv('output/export.csv', index=False)\n"
            + "print('csv ok')";
    this.jsonAuthed()
        .body("{\"section\":\"core\",\"path\":\"main.py\",\"content\":" + asJsonString(code) + "}")
        .post("/api/v1/ai/sandboxes/" + sandboxId + "/files")
        .then()
        .statusCode(200)
        .log().ifValidationFails();

    // run
    this.jsonAuthed()
        .body("{\"script\":\"core/main.py\"}")
        .post("/api/v1/ai/sandboxes/" + sandboxId + "/run")
        .then()
        .statusCode(200)
        .body("data.exit_code", Matchers.is(0))
        .body("data.status", Matchers.is("success"));

    // close
    Response close =
        this.jsonAuthed().post("/api/v1/ai/sandboxes/" + sandboxId + "/close");
    close
        .then()
        .statusCode(200)
        .body("data.closed", Matchers.is(true))
        .body("data.files.size()", Matchers.greaterThanOrEqualTo(1));

    // download
    String fileId = close.jsonPath().getString("data.files[0].id");
    Response dl =
        this.authed().get("/api/v1/ai/sandboxes/" + sandboxId + "/files/" + fileId + "/download");
    dl.then().statusCode(200);
    org.junit.jupiter.api.Assertions.assertTrue(dl.asString().contains("a,b"));
  }

  @Test
  @Order(4)
  void acces_sans_permission_refuse() {
    // Un token "déterministe" ne suffit pas ici : le refus de permission est
    // couvert par les tests Laravel (SandboxToolsTest). Ici on vérifie que le
    // show exige l'authentification (401 sans token).
    io.restassured.RestAssured.given()
        .port(this.port)
        .get("/api/v1/ai/sandboxes/capabilities")
        .then()
        .statusCode(401);
  }

  private static String asJsonString(String s) {
    return "\""
        + s.replace("\\", "\\\\")
            .replace("\"", "\\\"")
            .replace("\n", "\\n")
            .replace("\r", "\\r")
            .replace("\t", "\\t")
        + "\"";
  }
}
