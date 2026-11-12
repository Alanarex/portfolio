<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class CaseStudyViewTest extends TestCase
{
    public function test_case_study_template_renders_only_provided_blocks(): void
    {
        $html = view('projects.show', ['project' => [
            'title' => 'Projet exemple',
            'summary' => 'Résumé.',
            'stack' => ['PHP', 'Laravel'],
            'sections' => [['id' => 'contexte', 'title' => 'Contexte', 'body' => 'Texte du contexte.']],
        ]])->render();

        $this->assertStringContainsString('Projet exemple', $html);
        $this->assertStringContainsString('PHP · Laravel', $html);
        $this->assertStringContainsString('href="#contexte"', $html);
        $this->assertStringNotContainsString('case__gallery', $html);
        $this->assertStringNotContainsString('case__pager', $html);
        $this->assertStringNotContainsString('Voir le site', $html);
    }

    public function test_case_study_template_escapes_content(): void
    {
        $html = view('projects.show', ['project' => ['title' => '<script>alert(1)</script>']])->render();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
