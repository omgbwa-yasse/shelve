package com.shelve.security;
import com.shelve.common.Json;

import jakarta.servlet.Filter;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.annotation.web.configurers.AuthorizeHttpRequestsConfigurer;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;

@Configuration
@EnableWebSecurity
public class SecurityConfig {
  private final SanctumTokenFilter sanctumTokenFilter;

  public SecurityConfig(SanctumTokenFilter sanctumTokenFilter) {
    this.sanctumTokenFilter = sanctumTokenFilter;
  }

  @Bean
  public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
    http.csrf(csrf -> csrf.disable())
        .sessionManagement(sm -> sm.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
        .authorizeHttpRequests(
            auth ->
                ((AuthorizeHttpRequestsConfigurer.AuthorizedUrl)
                        ((AuthorizeHttpRequestsConfigurer.AuthorizedUrl)
                                ((AuthorizeHttpRequestsConfigurer.AuthorizedUrl)
                                        auth.requestMatchers(
                                            new String[] {
                                              "/api/v1/auth/login",
                                              "/swagger-ui/**",
                                              "/v3/api-docs/**",
                                              "/swagger-ui.html"
                                            }))
                                    .permitAll()
                                    .requestMatchers(new String[] {"/actuator/health"}))
                            .permitAll()
                            .anyRequest())
                    .authenticated())
        .exceptionHandling(
            ex ->
                ex.authenticationEntryPoint(
                    (request, response, authException) -> {
                      response.setStatus(401);
                      response.setContentType("application/json");
                      response.getWriter().write("{\"message\":\"Unauthenticated.\"}");
                    }))
        .addFilterBefore(
            (Filter) this.sanctumTokenFilter, UsernamePasswordAuthenticationFilter.class);
    return (SecurityFilterChain) http.build();
  }

  @Bean
  public PasswordEncoder passwordEncoder() {
    return new BCryptPasswordEncoder();
  }
}
