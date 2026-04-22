FROM ubuntu:22.04

RUN apt-get update && \
    apt-get install -y wget cmake make

COPY . /app

WORKDIR /app

RUN make php/install && \
    make composer/install/prod && \
    make dev/PrometheusExporter.phar

FROM alpine

COPY --from=0 /app/dev/PrometheusExporter.phar /PrometheusExporter.phar



