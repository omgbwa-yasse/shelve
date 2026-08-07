package com.shelve.common;

import com.shelve.common.PageEnvelope;
import jakarta.servlet.http.HttpServletRequest;
import java.util.List;
import java.util.Map;
import java.util.function.Function;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;

public final class Paging {
  private Paging() {}

  public static <T, V> Map<String, Object> page(
      JpaSpecificationExecutor<T> repo,
      Specification<T> spec,
      QueryParams qp,
      List<String> sortable,
      String defaultSort,
      HttpServletRequest request,
      Function<T, V> toView) {
    Sort sort = Paging.toSort(qp, sortable, defaultSort);
    Page page =
        repo.findAll(
            spec,
            (Pageable)
                PageRequest.of(
                    (int) (qp.getPageNumber() - 1), (int) qp.getPageSize(), (Sort) sort));
    List<V> items = page.getContent().stream().map(toView).toList();
    String baseUrl = Paging.requestBaseUrl(request);
    return PageEnvelope.build(
        items, page.getTotalElements(), qp.getPageNumber(), qp.getPageSize(), baseUrl);
  }

  private static Sort toSort(QueryParams qp, List<String> sortable, String defaultSort) {
    List<QueryParams.SortField> fields = qp.getSort();
    if (fields.isEmpty()) {
      return Sort.by((Sort.Direction) Sort.Direction.ASC, (String[]) new String[] {defaultSort});
    }
    Sort.Order[] orders =
        (Sort.Order[])
            fields.stream()
                .map(
                    sf ->
                        sf.descending()
                            ? Sort.Order.desc((String) sf.field())
                            : Sort.Order.asc((String) sf.field()))
                .toArray(Sort.Order[]::new);
    return Sort.by((Sort.Order[]) orders);
  }

  private static String requestBaseUrl(HttpServletRequest request) {
    String scheme = request.getScheme();
    int port = request.getServerPort();
    String host = request.getServerName();
    String portPart =
        scheme.equals("http") && port == 80 || scheme.equals("https") && port == 443
            ? ""
            : ":" + port;
    return scheme + "://" + host + portPart + request.getRequestURI();
  }
}
