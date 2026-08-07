package com.shelve.ai.sandbox.config;

import org.springframework.boot.context.properties.ConfigurationProperties;

@ConfigurationProperties(prefix = "app.sandbox")
public class SandboxProperties {
  private String root;
  private String capabilities;
  private int timeoutSeconds = 120;
  private int ttlHours = 24;

  public String getRoot() {
    return this.root;
  }

  public void setRoot(String v) {
    this.root = v;
  }

  public String getCapabilities() {
    return this.capabilities;
  }

  public void setCapabilities(String v) {
    this.capabilities = v;
  }

  public int getTimeoutSeconds() {
    return this.timeoutSeconds;
  }

  public void setTimeoutSeconds(int v) {
    this.timeoutSeconds = v;
  }

  public int getTtlHours() {
    return this.ttlHours;
  }

  public void setTtlHours(int v) {
    this.ttlHours = v;
  }
}
