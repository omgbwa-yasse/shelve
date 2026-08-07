package com.shelve.dolly.controller;

import com.shelve.exception.ApiException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import java.util.LinkedHashMap;
import java.util.Map;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/dollies/action"})
public class DollyActionController {
  @GetMapping
  public Map<String, Object> index() {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "dolly_viewAny");
    LinkedHashMap<String, Object> body = new LinkedHashMap<String, Object>();
    body.put("type", "about:blank");
    body.put("title", "Non impl\u00e9ment\u00e9");
    body.put("status", 501);
    body.put(
        "detail",
        "Le routeur d'actions en masse de DollyActionController n'est pas expos\u00e9 : utiliser"
            + " les points d'entr\u00e9e explicites add-*/remove-*, clear et rename.");
    throw new ApiException(HttpStatus.NOT_IMPLEMENTED, "Non impl\u00e9ment\u00e9");
  }
}
