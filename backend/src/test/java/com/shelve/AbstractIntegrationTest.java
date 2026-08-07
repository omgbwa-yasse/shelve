/*
 * Decompiled with CFR 0.152.
 *
 * Could not load the following classes:
 *  io.restassured.RestAssured
 *  io.restassured.http.ContentType
 *  io.restassured.response.Response
 *  io.restassured.specification.RequestSpecification
 *  org.junit.jupiter.api.BeforeAll
 *  org.junit.jupiter.api.BeforeEach
 *  org.springframework.boot.test.context.SpringBootTest
 *  org.springframework.boot.test.context.SpringBootTest$WebEnvironment
 *  org.springframework.boot.test.web.server.LocalServerPort
 *  org.springframework.test.context.ActiveProfiles
 */
package com.shelve;

import io.restassured.RestAssured;
import io.restassured.http.ContentType;
import io.restassured.response.Response;
import io.restassured.specification.RequestSpecification;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.boot.test.web.server.LocalServerPort;
import org.springframework.test.context.ActiveProfiles;

@SpringBootTest(webEnvironment = SpringBootTest.WebEnvironment.RANDOM_PORT)
@ActiveProfiles(value = {"test"})
public abstract class AbstractIntegrationTest {
  @LocalServerPort protected int port;
  protected static final String EMAIL = "conformance@shelve.test";
  protected static final String PASSWORD = "conformance-secret";

  @BeforeAll
  static void setupRestAssured() {
    RestAssured.enableLoggingOfRequestAndResponseIfValidationFails();
  }

  @BeforeEach
  void bindPort() {
    RestAssured.port = this.port;
    RestAssured.basePath = "";
  }

  protected Response login() {
    return (Response)
        RestAssured.given()
            .port(this.port)
            .contentType(ContentType.JSON)
            .accept(ContentType.JSON)
            .body(
                "{\"email\":\"conformance@shelve.test\",\"password\":\"conformance-secret\",\"device_name\":\"junit\"}")
            .post("/api/v1/auth/login", new Object[0]);
  }

  protected String token() {
    return this.login().jsonPath().getString("data.token");
  }

  protected RequestSpecification authed() {
    return RestAssured.given()
        .port(this.port)
        .accept(ContentType.JSON)
        .header("Authorization", (Object) ("Bearer " + this.token()), new Object[0]);
  }

  protected RequestSpecification jsonAuthed() {
    return this.authed().contentType(ContentType.JSON);
  }
}
