package com.shelve.seed;

import com.shelve.ai.entity.Prompt;
import com.shelve.ai.repository.PromptRepository;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Module IA / Assistant — prompt système par défaut (lecture seule, recherche et
 * lecture de notices). Transposition du PromptSeeder Laravel (socle minimal).
 * Idempotent.
 */
@Component
public class AiModuleSeeder {
  private static final Logger log = LoggerFactory.getLogger(AiModuleSeeder.class);

  private final PromptRepository promptRepository;

  public AiModuleSeeder(PromptRepository promptRepository) {
    this.promptRepository = promptRepository;
  }

  @Transactional
  public void run() {
    boolean exists =
        this.promptRepository
            .findAll(
                (root, q, cb) ->
                    cb.equal(root.get("title"), "Assistant SHELVE — prompt système"))
            .stream()
            .findAny()
            .isPresent();
    if (exists) return;

    Prompt p = new Prompt();
    p.setTitle("Assistant SHELVE — prompt système");
    p.setContent(
        "Vous êtes l'assistant IA de SHELVE, un système de gestion d'archives.\n"
            + "Vous répondez en français, de façon concise et utile.\n"
            + "Vos seules actions sont la recherche de notices (search) et la lecture d'une notice (show).\n"
            + "Vous n'avez jamais le droit de créer, modifier ou supprimer des données.");
    p.setIsSystem(true);
    this.promptRepository.save(p);
    log.info("Module IA : prompt système par défaut créé.");
  }
}
