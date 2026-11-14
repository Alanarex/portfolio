<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Enums;

enum HotspotKey: string
{
    case MainMonitor = 'main_monitor';
    case SecondaryMonitor = 'secondary_monitor';
    case Bookshelf = 'bookshelf';
    case Certificates = 'certificates';
    case Calendar = 'calendar';
    case KanbanBoard = 'kanban_board';
    case ServerRack = 'server_rack';
    case Phone = 'phone';
    case AirplaneModel = 'airplane_model';
    case CarModel = 'car_model';
    case FishingObject = 'fishing_object';

    /** Landing-page section anchors a hotspot may point to. */
    public const TARGETS = ['projets', 'activite', 'competences', 'formation', 'experience', 'contact', 'interets'];

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }

    public function defaultTarget(): string
    {
        return match ($this) {
            self::MainMonitor => 'projets',
            self::SecondaryMonitor => 'activite',
            self::Bookshelf, self::KanbanBoard, self::ServerRack => 'competences',
            self::Certificates => 'formation',
            self::Calendar => 'experience',
            self::Phone => 'contact',
            self::AirplaneModel, self::CarModel, self::FishingObject => 'interets',
        };
    }

    public function defaultOrder(): int
    {
        return (array_search($this, self::cases(), true) + 1) * 10;
    }
}
