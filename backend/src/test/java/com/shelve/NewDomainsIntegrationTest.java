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

class NewDomainsIntegrationTest extends AbstractIntegrationTest {
  NewDomainsIntegrationTest() {}

  private int suffix() {
    return ThreadLocalRandom.current().nextInt(10000, 99999);
  }

  private int currentUserId() {
    return this.login().jsonPath().getInt("data.user.id");
  }

  private int currentOrgId() {
    return this.login().jsonPath().getInt("data.user.current_organisation_id");
  }

  @Test
  void slips_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((ValidatableResponse)
                                        ((Response)
                                                this.jsonAuthed()
                                                    .body(
                                                        "{\"code\":\"SLP"
                                                            + suffix
                                                            + "\",\"name\":\"Bordereau "
                                                            + suffix
                                                            + "\",\"user_organisation_id\":"
                                                            + this.currentOrgId()
                                                            + "}")
                                                    .post("/api/v1/slips", new Object[0]))
                                            .then())
                                    .statusCode(201))
                            .body(
                                "data.code",
                                Matchers.equalTo((Object) ("SLP" + suffix)),
                                new Object[0]))
                    .body("data.records_count", Matchers.notNullValue(), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response) this.authed().get("/api/v1/slips/" + id, new Object[0])).then())
                .statusCode(200))
        .body("data.id", Matchers.equalTo((Object) id), new Object[0]);
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.jsonAuthed()
                                .body("{\"name\":\"Bordereau renomm\u00e9\"}")
                                .patch("/api/v1/slips/" + id, new Object[0]))
                        .then())
                .statusCode(200))
        .body("data.name", Matchers.equalTo((Object) "Bordereau renomm\u00e9"), new Object[0]);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/slips/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void slipStatuses_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body("{\"name\":\"Statut " + suffix + "\"}")
                                            .post("/api/v1/slip-statuses", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.slips_count", Matchers.notNullValue(), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/slip-statuses/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void communications_crud() {
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"name\":\"Communication\",\"user_id\":"
                                                    + this.currentUserId()
                                                    + ",\"user_organisation_id\":"
                                                    + this.currentOrgId()
                                                    + ",\"return_date\":\"2026-09-01\",\"status\":\"pending\"}")
                                            .post("/api/v1/communications", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.code", Matchers.startsWith((String) "C2026"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/communications/" + id, new Object[0])).then())
        .statusCode(200);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/communications/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void reservations_crud() {
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"name\":\"R\u00e9servation\",\"user_id\":"
                                                    + this.currentUserId()
                                                    + ",\"user_organisation_id\":"
                                                    + this.currentOrgId()
                                                    + ",\"status\":\"pending\"}")
                                            .post("/api/v1/reservations", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.code", Matchers.startsWith((String) "R2026"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/reservations/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void retentions_crud() {
    int suffix = this.suffix();
    int sortId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body("{\"code\":\"E\",\"name\":\"Sort " + suffix + "\"}")
                                    .post("/api/v1/sorts", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"code\":\"RET"
                                                    + suffix
                                                    + "\",\"name\":\"R\u00e9tention "
                                                    + suffix
                                                    + "\",\"duration\":5,\"sort_id\":"
                                                    + sortId
                                                    + "}")
                                            .post("/api/v1/retentions", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.duration", Matchers.equalTo((Object) 5), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/retentions/" + id, new Object[0])).then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/sorts/" + sortId, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void workflowDefinitions_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"name\":\"Workflow "
                                                    + suffix
                                                    + "\",\"bpmn_xml\":\"<bpmn/>\",\"status\":\"draft\"}")
                                            .post("/api/v1/workflow-definitions", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.name", Matchers.startsWith((String) "Workflow "), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/workflow-definitions/" + id, new Object[0]))
                .then())
        .statusCode(200);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/workflow-definitions/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void aiSkills_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"slug\":\"skill-"
                                                    + suffix
                                                    + "\",\"name\":\"Skill "
                                                    + suffix
                                                    + "\"}")
                                            .post("/api/v1/ai-skills", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.slug", Matchers.startsWith((String) "skill-"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/ai-skills/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void prompts_crud() {
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"title\":\"Prompt\",\"content\":\"Contenu du prompt\"}")
                                    .post("/api/v1/prompts", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/prompts/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void logs_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"description\":\"Action "
                                            + suffix
                                            + "\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"junit\"}")
                                    .post("/api/v1/logs", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/logs/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void mailActions_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"name\":\"Action "
                                                    + suffix
                                                    + "\",\"duration\":3,\"description\":\"D\u00e9cision\"}")
                                            .post("/api/v1/mail-actions", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.to_return", Matchers.notNullValue(), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/mail-actions/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void mailPriorities_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"name\":\"Priorit\u00e9 "
                                            + suffix
                                            + "\",\"duration\":2}")
                                    .post("/api/v1/mail-priorities", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/mail-priorities/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void mailTypologies_crud() {
    int suffix = this.suffix();
    int activityId =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"code\":\"ACT"
                                            + suffix
                                            + "\",\"name\":\"Activit\u00e9 "
                                            + suffix
                                            + "\"}")
                                    .post("/api/v1/activities", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"code\":\"T"
                                            + suffix % 10000
                                            + "\",\"name\":\"Typologie "
                                            + suffix
                                            + "\",\"activity_id\":"
                                            + activityId
                                            + "}")
                                    .post("/api/v1/mail-typologies", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/mail-typologies/" + id, new Object[0]))
                .then())
        .statusCode(204);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/activities/" + activityId, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void dollies_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"name\":\"Chariot "
                                                    + suffix
                                                    + "\",\"description\":\"Test\",\"category\":\"record\"}")
                                            .post("/api/v1/dollies", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.category", Matchers.equalTo((Object) "record"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.jsonAuthed()
                                .body("{\"name\":\"Chariot renomm\u00e9\"}")
                                .post("/api/v1/dollies/" + id + "/rename", new Object[0]))
                        .then())
                .statusCode(200))
        .body("data.name", Matchers.equalTo((Object) "Chariot renomm\u00e9"), new Object[0]);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/dollies/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void recordStatuses_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body("{\"name\":\"Statut " + suffix + "\"}")
                                    .post("/api/v1/record-statuses", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/record-statuses/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void recordConfidentialities_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"code\":\"CONF"
                                            + suffix
                                            + "\",\"name\":\"Confidentialit\u00e9 "
                                            + suffix
                                            + "\"}")
                                    .post("/api/v1/record-confidentialities", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response)
                    this.authed().delete("/api/v1/record-confidentialities/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void thesaurusSchemes_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"uri\":\"http://exemple.test/scheme-"
                                            + suffix
                                            + "\",\"title\":\"Sch\u00e9ma "
                                            + suffix
                                            + "\"}")
                                    .post("/api/v1/thesaurus-schemes", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/thesaurus-schemes/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void projects_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"code\":\"PRJ"
                                                    + suffix
                                                    + "\",\"name\":\"Projet "
                                                    + suffix
                                                    + "\",\"attachable_type\":\"App\\\\Models\\\\Organisation\",\"attachable_id\":"
                                                    + this.currentOrgId()
                                                    + "}")
                                            .post("/api/v1/projects", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.code", Matchers.startsWith((String) "PRJ"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/projects/" + id, new Object[0])).then())
        .statusCode(200);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/projects/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void objectives_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"title\":\"Objectif "
                                            + suffix
                                            + "\",\"attachable_type\":\"App\\\\Models\\\\Organisation\",\"attachable_id\":"
                                            + this.currentOrgId()
                                            + "}")
                                    .post("/api/v1/objectives", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/objectives/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void aiConversations_crud() {
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body("{\"title\":\"Conversation\"}")
                                    .post("/api/v1/ai/conversations", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/ai/conversations/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }

  @Test
  void records_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((ValidatableResponse)
                                        ((ValidatableResponse)
                                                ((ValidatableResponse)
                                                        ((Response)
                                                                this.jsonAuthed()
                                                                    .body(
                                                                        "{\"code\":\"REC"
                                                                            + suffix
                                                                            + "\",\"name\":\"Notice"
                                                                            + " "
                                                                            + suffix
                                                                            + "\",\"access_level\":\"internal\"}")
                                                                    .post(
                                                                        "/api/v1/records",
                                                                        new Object[0]))
                                                            .then())
                                                    .statusCode(201))
                                            .body(
                                                "data.code",
                                                Matchers.startsWith((String) "REC"),
                                                new Object[0]))
                                    .body(
                                        "data.is_root", Matchers.is((Object) true), new Object[0]))
                            .body(
                                "data.is_current_version",
                                Matchers.is((Object) true),
                                new Object[0]))
                    .body("data.version_number", Matchers.equalTo((Object) 1), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().get("/api/v1/records/" + id, new Object[0])).then())
        .statusCode(200);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/records/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void records_recherche() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body(
                                        "{\"code\":\"RCH"
                                            + suffix
                                            + "\",\"name\":\"Recherche test "
                                            + suffix
                                            + "\"}")
                                    .post("/api/v1/records", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((ValidatableResponse)
                    ((Response)
                            this.authed()
                                .queryParam("q", new Object[] {"Recherche test"})
                                .get("/api/v1/search/records", new Object[0]))
                        .then())
                .statusCode(200))
        .body("data", Matchers.isA(List.class), new Object[0]);
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/records/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void workplaces_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body(
                                                "{\"code\":\"WP"
                                                    + suffix
                                                    + "\",\"name\":\"Espace "
                                                    + suffix
                                                    + "\"}")
                                            .post("/api/v1/workplaces", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.code", Matchers.startsWith((String) "WP"), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/workplaces/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void tasks_crud() {
    int suffix = this.suffix();
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((ValidatableResponse)
                                ((Response)
                                        this.jsonAuthed()
                                            .body("{\"title\":\"T\u00e2che " + suffix + "\"}")
                                            .post("/api/v1/tasks", new Object[0]))
                                    .then())
                            .statusCode(201))
                    .body("data.title", Matchers.startsWith((String) "T\u00e2che "), new Object[0]))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response) this.authed().delete("/api/v1/tasks/" + id, new Object[0])).then())
        .statusCode(204);
  }

  @Test
  void workplaceConversations_crud() {
    int id =
        ((ValidatableResponse)
                ((ValidatableResponse)
                        ((Response)
                                this.jsonAuthed()
                                    .body("{\"name\":\"Discussion\"}")
                                    .post("/api/v1/workplace-conversations", new Object[0]))
                            .then())
                    .statusCode(201))
            .extract()
            .jsonPath()
            .getInt("data.id");
    ((ValidatableResponse)
            ((Response)
                    this.authed().delete("/api/v1/workplace-conversations/" + id, new Object[0]))
                .then())
        .statusCode(204);
  }
}
