# Stage 1: get RoadRunner binary
FROM spiralscout/roadrunner:2025.1.4 AS rr

# Stage 2: PHP 8.4 with Composer, plus RoadRunner binary copied in
FROM php:8.4-cli-alpine
LABEL authors="Helio"

# Copy RoadRunner binary from the rr image
COPY --from=rr /usr/bin/rr /usr/local/bin/rr

# Add Composer (copy the single binary from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Ensure binaries are executable
RUN chmod +x /usr/local/bin/rr /usr/local/bin/composer

# Keep the simple top-based entrypoint unless overridden by compose or runtime command
ENTRYPOINT ["top", "-b"]