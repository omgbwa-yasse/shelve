package com.shelve.mails.service;

import com.shelve.exception.ApiException;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;

/**
 * Garde des capacités courrier externe — parité avec le middleware Laravel
 * `EnsureMailExternalCapability` (`mail.external:send` / `mail.external:receive`).
 *
 * Les contrôleurs exposant l'envoi/réception de courrier externe doivent appeler
 * {@link #checkSend()} / {@link #checkReceive()} avant toute action : un
 * utilisateur dont l'organisation courante n'a pas la capacité reçoit un 403
 * (« Le courrier externe n'est pas activé pour votre organisation. »).
 */
@Service
public class MailCapabilityService {
  private final OrganisationRepository organisationRepository;

  public MailCapabilityService(OrganisationRepository organisationRepository) {
    this.organisationRepository = organisationRepository;
  }

  public void checkSend() {
    checkCapability(true);
  }

  public void checkReceive() {
    checkCapability(false);
  }

  private void checkCapability(boolean send) {
    AuthenticatedUser auth = CurrentUser.get();
    if (auth.isSuperAdmin()) {
      return;
    }
    Organisation organisation =
        auth.user().getCurrentOrganisationId() != null
            ? this.organisationRepository
                .findById(auth.user().getCurrentOrganisationId())
                .orElse(null)
            : null;
    boolean enabled =
        send
            ? organisation != null && Boolean.TRUE.equals(organisation.getMailExternalSendEnabled())
            : organisation != null
                && Boolean.TRUE.equals(organisation.getMailExternalReceiveEnabled());
    if (!enabled) {
      throw new ApiException(
          HttpStatus.FORBIDDEN, "Le courrier externe n'est pas activ\u00e9 pour votre organisation.");
    }
  }
}
