package com.shelve.organisation.service;

import com.shelve.organisation.entity.PersonalAccessToken;
import com.shelve.organisation.repository.PersonalAccessTokenRepository;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.util.HexFormat;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class TokenService {
  private static final String TOKENABLE_TYPE = "App\\Models\\User";
  private static final String ALPHABET =
      "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  private static final int TOKEN_LENGTH = 40;
  private static final SecureRandom RANDOM = new SecureRandom();
  private final PersonalAccessTokenRepository tokenRepository;

  public TokenService(PersonalAccessTokenRepository tokenRepository) {
    this.tokenRepository = tokenRepository;
  }

  @Transactional
  public String createToken(Long userId, String name) {
    String plain = TokenService.randomToken();
    PersonalAccessToken token = new PersonalAccessToken();
    token.setTokenableType(TOKENABLE_TYPE);
    token.setTokenableId(userId);
    token.setName(name);
    token.setToken(TokenService.sha256(plain));
    token.setAbilities("[\"*\"]");
    this.tokenRepository.save(token);
    return token.getId() + "|" + plain;
  }

  @Transactional
  public void revoke(Long tokenId) {
    this.tokenRepository.findById(tokenId).ifPresent(arg_0 -> this.tokenRepository.delete(arg_0));
  }

  @Transactional
  public void revokeAllForUser(Long userId) {
    this.tokenRepository.findAll().stream()
        .filter(
            t -> TOKENABLE_TYPE.equals(t.getTokenableType()) && t.getTokenableId().equals(userId))
        .forEach(arg_0 -> this.tokenRepository.delete(arg_0));
  }

  private static String randomToken() {
    StringBuilder sb = new StringBuilder(40);
    for (int i = 0; i < 40; ++i) {
      sb.append(ALPHABET.charAt(RANDOM.nextInt(ALPHABET.length())));
    }
    return sb.toString();
  }

  private static String sha256(String value) {
    try {
      MessageDigest digest = MessageDigest.getInstance("SHA-256");
      return HexFormat.of().formatHex(digest.digest(value.getBytes(StandardCharsets.UTF_8)));
    } catch (NoSuchAlgorithmException e) {
      throw new IllegalStateException("SHA-256 indisponible", e);
    }
  }
}
