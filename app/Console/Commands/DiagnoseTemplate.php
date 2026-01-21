<?php

namespace App\Console\Commands;

use App\Models\MessageTemplate;
use Illuminate\Console\Command;

class DiagnoseTemplate extends Command
{
    protected $signature = 'template:diagnose {id : Template ID}';
    protected $description = 'Diagnose a template to check for issues with variables';

    public function handle(): int
    {
        $id = $this->argument('id');
        $template = MessageTemplate::find($id);

        if (!$template) {
            $this->error("Template with ID {$id} not found");
            return 1;
        }

        $this->info("=== Template: {$template->name} ===");
        $this->line("ID: {$template->id}");
        $this->line("Language: {$template->language}");
        $this->line("Status: {$template->status}");
        $this->line("Category: {$template->category}");

        $components = $template->components;

        $this->newLine();
        $this->info("=== Raw Components ===");
        $this->line(json_encode($components, JSON_PRETTY_PRINT));

        // Check if it's array format or object format
        $isArrayFormat = isset($components[0]) && isset($components[0]['type']);
        $this->newLine();
        $this->line("Format: " . ($isArrayFormat ? "Array (Meta format)" : "Object (local format)"));

        // Extract all variables
        $this->newLine();
        $this->info("=== Variables Analysis ===");

        $allVariables = [];

        if ($isArrayFormat) {
            foreach ($components as $comp) {
                $type = strtoupper($comp['type'] ?? '');
                $text = $comp['text'] ?? '';

                if ($text) {
                    preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $varNum) {
                            $varKey = strtolower($type) . "_{$varNum}";
                            $allVariables[$varKey] = [
                                'component' => $type,
                                'index' => $varNum,
                                'key' => $varKey,
                            ];
                        }
                        $this->line("{$type}: Found variables " . implode(', ', $matches[0]));
                    } else {
                        $this->line("{$type}: No variables");
                    }
                }

                // Check for media header
                if ($type === 'HEADER') {
                    $format = $comp['format'] ?? 'TEXT';
                    if (in_array($format, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                        $allVariables['header_media_url'] = [
                            'component' => 'HEADER',
                            'type' => $format,
                            'key' => 'header_media_url',
                        ];
                        $this->line("HEADER ({$format}): Requires header_media_url");
                    }
                }
            }
        } else {
            // Object format
            foreach (['header', 'body', 'footer'] as $section) {
                if (isset($components[$section]['text'])) {
                    preg_match_all('/\{\{(\d+)\}\}/', $components[$section]['text'], $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $varNum) {
                            $varKey = "{$section}_{$varNum}";
                            $allVariables[$varKey] = [
                                'component' => strtoupper($section),
                                'index' => $varNum,
                                'key' => $varKey,
                            ];
                        }
                        $this->line(strtoupper($section) . ": Found variables " . implode(', ', $matches[0]));
                    }
                }
            }

            if (isset($components['header']['format'])) {
                $format = $components['header']['format'];
                if (in_array($format, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                    $allVariables['header_media_url'] = [
                        'component' => 'HEADER',
                        'type' => $format,
                        'key' => 'header_media_url',
                    ];
                    $this->line("HEADER ({$format}): Requires header_media_url");
                }
            }
        }

        $this->newLine();
        $this->info("=== Required Variables for Campaign ===");
        if (empty($allVariables)) {
            $this->line("This template has NO variables - send without components");
        } else {
            $this->table(
                ['Key', 'Component', 'Details'],
                array_map(fn($v) => [$v['key'], $v['component'], $v['index'] ?? $v['type'] ?? ''], $allVariables)
            );

            $this->newLine();
            $this->warn("When creating a campaign with this template, you MUST provide mappings for:");
            foreach ($allVariables as $var) {
                $this->line("  - {$var['key']}");
            }
        }

        return 0;
    }
}
