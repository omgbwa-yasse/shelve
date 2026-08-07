package com.shelve.search.controller;

import com.shelve.common.Json;
import com.shelve.common.PageEnvelope;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.QueryParams;
import com.shelve.records.entity.Record;
import com.shelve.records.repository.RecordRepository;
import jakarta.persistence.criteria.Predicate;
import jakarta.servlet.http.HttpServletRequest;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * D10 — recherche. `GET /search/records?q=...` recherche dans les notices de
 * l'organisation courante (nom, code, description, métadonnées JSON).
 */
@RestController
@RequestMapping("/api/v1/search")
public class SearchController {

    private final RecordRepository recordRepository;

    public SearchController(RecordRepository recordRepository) {
        this.recordRepository = recordRepository;
    }

    @GetMapping("/records")
    public Map<String, Object> records(HttpServletRequest request) {
        AuthenticatedUser auth = CurrentUser.get();
        Policy.check(auth, "record_viewAny");

        String q = request.getParameter("q");
        Long orgId = auth.user().getCurrentOrganisationId();

        Specification<Record> spec = (root, query, cb) -> {
            List<Predicate> preds = new ArrayList<>();
            preds.add(cb.equal(root.get("organisationId"), orgId));
            preds.add(cb.equal(root.get("isCurrentVersion"), true));
            if (q != null && !q.isBlank()) {
                for (String term : q.trim().split("\\s+")) {
                    String like = "%" + term + "%";
                    preds.add(cb.or(
                            cb.like(cb.lower(root.get("name")), like.toLowerCase()),
                            cb.like(cb.lower(root.get("code")), like.toLowerCase()),
                            cb.like(cb.lower(root.get("description")), like.toLowerCase()),
                            cb.like(cb.lower(root.get("metadata")), like.toLowerCase())));
                }
            }
            return cb.and(preds.toArray(new Predicate[0]));
        };

        QueryParams qp = QueryParams.parse(request);
        Page<Record> page = recordRepository.findAll(spec, PageRequest.of(qp.getPageNumber() - 1, qp.getPageSize(),
                Sort.by(Sort.Direction.DESC, "updatedAt")));

        List<Map<String, Object>> items = page.getContent().stream().map(this::brief).toList();
        String baseUrl = request.getScheme() + "://" + request.getServerName() + ":" + request.getServerPort()
                + request.getRequestURI();
        return PageEnvelope.build(items, page.getTotalElements(), qp.getPageNumber(), qp.getPageSize(), baseUrl);
    }

    private Map<String, Object> brief(Record r) {
        Map<String, Object> map = new LinkedHashMap<>();
        map.put("id", r.getId());
        map.put("code", r.getCode());
        map.put("name", r.getName());
        map.put("type_id", r.getTypeId());
        map.put("level_id", r.getLevelId());
        map.put("status_id", r.getStatusId());
        map.put("organisation_id", r.getOrganisationId());
        map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
        return map;
    }
}
