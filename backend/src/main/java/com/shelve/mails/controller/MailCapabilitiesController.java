package com.shelve.mails.controller;

import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import java.util.LinkedHashMap;
import java.util.Map;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Capacités courrier externe de l'organisation courante — parité avec Laravel
 * (`MailController::externalCapabilities`, GET /api/v1/mails/external-capabilities).
 *
 * Consommé par Next.js pour masquer les boutons « Envoyer (externe) » /
 * « Recevoir (externe) » quand la capacité est désactivée pour l'organisation
 * (`organisations.mail_external_send_enabled` / `mail_external_receive_enabled`).
 */
@RestController
@RequestMapping(value = {"/api/v1/mails"})
public class MailCapabilitiesController {
  private final OrganisationRepository organisationRepository;

  public MailCapabilitiesController(OrganisationRepository organisationRepository) {
    this.organisationRepository = organisationRepository;
  }

  @GetMapping(value = {"/external-capabilities"})
  public Map<String, Object> externalCapabilities() {
    AuthenticatedUser auth = CurrentUser.get();
    Organisation organisation =
        auth.user().getCurrentOrganisationId() != null
            ? this.organisationRepository
                .findById(auth.user().getCurrentOrganisationId())
                .orElse(null)
            : null;

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put(
        "external_send_enabled",
        organisation != null && Boolean.TRUE.equals(organisation.getMailExternalSendEnabled()));
    data.put(
        "external_receive_enabled",
        organisation != null
            && Boolean.TRUE.equals(organisation.getMailExternalReceiveEnabled()));
    return Map.of("data", data);
  }
}
