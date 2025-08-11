### History Engine for Laravel

Speicherunabhängige History-Verwaltung mit Session/Cache/Redis-Storage und LNavi-kompatiblen String-Befehlen (`<<`, `<`, `>`, `>>`, `<>(n)`).

#### Installation

1) Paket installieren
```bash
composer require alex-kassel/history-engine
```

2) Konfiguration veröffentlichen (optional)
```bash
php artisan vendor:publish --tag=history-engine-config
```

`.env` Beispiele:
```
HISTORY_ENGINE_DRIVER=session  # session | cache | redis
HISTORY_ENGINE_PREFIX=history_engine:
HISTORY_ENGINE_CACHE_STORE=redis
HISTORY_ENGINE_REDIS_CONNECTION=default
HISTORY_ENGINE_TTL=86400
```

#### Verwendung

```php
use AlexKassel\HistoryEngine\Facades\HistoryEngine as History;

// Engine für einen Scope erzeugen (z. B. "search")
$engine = History::engine('search');

// Eintrag anfügen (Duplikate am Ende werden ignoriert)
$engine->record('.:h:c:>1000;:b:c:/~200..400');

// Navigation per LNavi-Steuerung
$engine->applyCommand('<<'); // Start
$engine->applyCommand('<');  // Schritt zurück
$engine->applyCommand('>');  // Schritt vor
$engine->applyCommand('>>'); // Ende
$engine->applyCommand('<>3'); // Zu Index 3 (0-basiert)

// Oder explizite Methoden
$engine->goToStart();
$engine->stepBack(2);
$engine->goToIndex(5);
$engine->goToEnd();

// Auslesen
$current = $engine->getCurrent();
$pointer = $engine->getPointer();
$all = $engine->getAll();

// Leeren
$engine->clear();
```

##### Treiber
- Session (Standard)
- Cache (Store/TTL konfigurierbar)
- Redis (Connection/TTL konfigurierbar)

##### Eigenen Treiber registrieren

```php
use AlexKassel\HistoryEngine\Contracts\HistoryStore;
use AlexKassel\HistoryEngine\HistoryManager;

class MyStore implements HistoryStore {
    public function load(string $key): array { /* ... */ }
    public function save(string $key, array $data): void { /* ... */ }
    public function clear(string $key): void { /* ... */ }
}

// App\Providers\AppServiceProvider::boot()
$this->app->afterResolving(HistoryManager::class, function (HistoryManager $manager) {
    $manager->extend('my', function ($app, array $cfg) {
        return new MyStore(/* ... */);
    });
});
```

#### Hinweise
- Indizierung ist 0-basiert (analog zur LNavi-Implementierung).
- Wird in der Mitte der History ein neuer Eintrag aufgenommen, wird der Vorwärts-Zweig abgeschnitten.

#### Veröffentlichung auf Packagist
1) Neues Git-Repo erstellen (z. B. GitHub) und Code pushen. `composer.json` mit PSR-4 prüfen.
2) Auf `packagist.org` einloggen und „Submit“ → Git-URL angeben.
3) Webhook/Auto-Updates aktivieren (GitHub Service Hook für Packagist).
4) Installation: `composer require alex-kassel/history-engine`.
