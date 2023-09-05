# PrometheusExporter

This exporter has been developed for use as a plugin on a [Pocketmine](https://github.com/pmmp/PocketMine-MP) server (> 5.0).

## Register custom metrics

```php
// In onLoad()

\Plutonium\PrometheusExporter\metrics\MetricsManager::registerMetric(new MyCustomMetric());
```

## Default metrics

| Name                                  | Description                              |
|---------------------------------------|------------------------------------------|
| `pocketmine_memory_heap_bytes`        | Memory usage of heap in bytes            |
| `pocketmine_memory_main_thread_bytes` | Memory usage of main thread in bytes     |
| `pocketmine_player_online_count`      | Count of online players                  |
| `pocketmine_thread_count`             | PocketMine thread count                  |
| `pocketmine_ticks_per_second`         | Indicates how much server lag spikes     |
| `pocketmine_tick_usage`               | How much tick using                      |
| `pocketmine_view_distance`            | View distance number repartition by size |
| `pocketmine_world_chunk_loaded`       | Loaded chunk count by world              |
| `pocketmine_world_entity_count`       | Count of entity by world                 |
| `pocketmine_world_loaded`             | Currently loaded world                   |
| `pocketmine_world_player_count`       | Count of player by world                 |
| `pocketmine_world_tick_rate`          | Tick rate time(millisecond) of world     |