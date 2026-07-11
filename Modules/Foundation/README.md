# Foundation module

`module.json` is the discovery contract. Each immediate child of `Modules/` may declare one service provider. Bootstrap validates and loads manifests in deterministic directory order. Modules own routes, migrations, configuration and business code; cross-module calls use explicit contracts or application services.

This module owns dependency-aware `/ready`; Laravel owns dependency-free `/up`.
