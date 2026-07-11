# Architecture modulaire

## Structure racine

```text
app/
├── Core/
│   ├── Contracts/
│   ├── Exceptions/
│   ├── Support/
│   ├── ValueObjects/
│   └── Shared/
│
Modules/
├── Auth/
├── Portfolio/
├── Projects/
├── Demos/
├── Features/
├── Tasks/
├── Kanban/
├── Calendar/
├── Statistics/
├── GitHub/
├── GitLab/
├── ModulesRegistry/
├── Deployment/
├── Licensing/
├── Monitoring/
├── ActivityLog/
├── Notifications/
└── Settings/
```

## Structure d'un module

```text
Modules/Projects/
├── Application/
│   ├── Actions/
│   ├── DTOs/
│   ├── Queries/
│   └── Services/
├── Domain/
│   ├── Contracts/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   └── ValueObjects/
├── Infrastructure/
│   ├── Repositories/
│   ├── Integrations/
│   └── Persistence/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   ├── Resources/
│   └── Policies/
├── Models/
├── Providers/
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   └── Seeders/
├── Routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── Resources/
│   ├── js/
│   ├── scss/
│   └── views/
├── Tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
├── Config/
├── module.json
└── README.md
```

## Règles

- Les contrôleurs restent fins.
- La logique métier est placée dans les Actions et Services applicatifs.
- Les états bornés utilisent des Enums.
- Les échanges entre couches utilisent des DTOs ou Value Objects.
- Les dépendances inter-modules passent par des contrats, services applicatifs ou événements.
- Les tests restent dans le module concerné.
- Les routes, migrations, assets et documentation d'un module restent avec ce module.
