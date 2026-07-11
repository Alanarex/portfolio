# Architecture système

## Vue générale

```text
Personal Platform
│
├── Public Portfolio
│   ├── Hero and positioning
│   ├── Featured projects and case studies
│   ├── Metrics and technical proof
│   ├── Low-poly 3D room preview + fullscreen mode
│   ├── Experience, skills and education
│   ├── GitHub/GitLab activity
│   ├── Personal Interests
│   └── Contact
│
├── Private Dashboard
│   ├── Portfolio CMS
│   ├── 3D scene metadata/assets/hotspots
│   ├── Projects and demos
│   ├── Workspace and feature tracking
│   ├── Repository analysis
│   ├── Deployment Center
│   ├── Licensing
│   ├── Module Registry
│   ├── Statistics and monitoring
│   └── Settings
│
├── Public API
│   ├── Portfolio API
│   ├── 3D scene configuration API
│   ├── Demo API
│   └── Contact API
│
├── Internal API
│   ├── Mobile API
│   ├── Licensing API
│   ├── Deployment API
│   └── Webhooks
│
└── Infrastructure
    ├── Docker
    ├── MySQL or PostgreSQL
    ├── Redis
    ├── Queue Workers
    ├── Scheduler
    ├── Nginx
    ├── Object/media storage
    ├── CI/CD
    ├── Staging
    └── Production
```

## Architectural direction

The project starts as a Laravel modular monolith. The public HTML content remains server-rendered and acts as the source of truth. Rich dashboard interfaces and the Three.js room are isolated interactive modules.

## 3D boundary

The 3D room is not responsible for core portfolio content. It consumes published configuration and links to normal HTML sections. A failure in WebGL, asset loading or animation must not break the landing page.
