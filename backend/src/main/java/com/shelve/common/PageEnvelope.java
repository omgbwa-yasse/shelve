package com.shelve.common;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

public class PageEnvelope {
  public static Map<String, Object> build(
      List<?> items, long total, int currentPage, int perPage, String baseUrl) {
    int lastPage =
        perPage == 0 ? 1 : (int) Math.max(1L, (long) Math.ceil((double) total / (double) perPage));
    LinkedHashMap<String, Number> meta = new LinkedHashMap<String, Number>();
    meta.put("current_page", currentPage);
    meta.put("per_page", perPage);
    meta.put("total", total);
    meta.put("last_page", lastPage);
    LinkedHashMap<String, String> links = new LinkedHashMap<String, String>();
    links.put("first", PageEnvelope.pageUrl(baseUrl, 1));
    links.put("prev", currentPage > 1 ? PageEnvelope.pageUrl(baseUrl, currentPage - 1) : null);
    links.put(
        "next", currentPage < lastPage ? PageEnvelope.pageUrl(baseUrl, currentPage + 1) : null);
    links.put("last", PageEnvelope.pageUrl(baseUrl, lastPage));
    LinkedHashMap<String, Object> envelope = new LinkedHashMap<String, Object>();
    envelope.put("data", items);
    envelope.put("meta", meta);
    envelope.put("links", links);
    return envelope;
  }

  private static String pageUrl(String baseUrl, int page) {
    return baseUrl + (baseUrl.contains("?") ? "&" : "?") + "page=" + page;
  }
}
