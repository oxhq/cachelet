# Changelog

## [v0.9.1] - Limpieza y saneamiento

- Eliminados métodos duplicados en `CacheletBuilder.php`.
- Removidos archivos vacíos o residuos de refactorización.
- Alineados todos los namespaces con estructura lógica (Support/Core separation).

### 📂 File Tree (src/)
├── Console
│   └── Commands
│       ├── CacheletFlushCommand.php
│       ├── CacheletInspectCommand.php
│       └── CacheletListCommand.php
├── Contracts
│   └── CacheletExecutorInterface.php
├── Core
│   ├── CacheletBuilder.php
│   ├── CacheletExecutor.php
│   ├── CacheletManager.php
│   ├── CoordinateLogger.php
│   ├── InvalidationOrchestrator.php
│   ├── KeyHasher.php
│   └── TtlParser.php
├── Facades
│   └── Cachelet.php
├── Normalizer
├── Observers
│   ├── CacheletModelObserver.php
│   └── RelationObserver.php
├── Strategies
│   ├── RegistryInvalidationStrategy.php
│   ├── TagInvalidationStrategy.php
│   ├── TokenGenerationStrategy.php
│   └── WildcardInvalidationStrategy.php
├── Support
│   └── Cachelet.php
├── Testing
│   └── ExpectCachelet.php
├── Traits
│   └── UsesCachelet.php
└── ValueObjects
    └── CacheletDefinition.php

