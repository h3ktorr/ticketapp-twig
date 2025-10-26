<?php
namespace Kelvin\TwigApp\Controllers;

class TicketController {

    private static function getTicketsFile(): string {
        // Use /tmp for writable storage
        $tmpFile = sys_get_temp_dir() . '/tickets.json';

        // Ensure the file exists
        if (!file_exists($tmpFile)) {
            file_put_contents($tmpFile, json_encode([]));
        }

        return $tmpFile;
    }

    // Fetch all tickets
    public static function all(): array {
        return json_decode(file_get_contents(self::getTicketsFile()), true) ?? [];
    }

    // Find a single ticket by ID
    public static function find($id): ?array {
        $tickets = self::all();
        foreach ($tickets as $ticket) {
            if ($ticket['id'] == $id) {
                return $ticket;
            }
        }
        return null;
    }

    // Create a new ticket
    public static function create(array $data): array {
        $tickets = self::all();
        $newTicket = [
            'id' => time(),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'open',
            'createdAt' => time(),
            'updatedAt' => time(),
        ];
        array_unshift($tickets, $newTicket);
        file_put_contents(self::getTicketsFile(), json_encode($tickets, JSON_PRETTY_PRINT));
        return $newTicket;
    }

    // Update a ticket by ID
    public static function update($id, array $data): ?array {
        $tickets = self::all();
        foreach ($tickets as $index => $ticket) {
            if ($ticket['id'] == $id) {
                $tickets[$index] = array_merge($ticket, $data, ['updatedAt' => time()]);
                file_put_contents(self::getTicketsFile(), json_encode($tickets, JSON_PRETTY_PRINT));
                return $tickets[$index];
            }
        }
        return null;
    }

    // Delete a ticket by ID
    public static function delete($id): bool {
        $tickets = self::all();
        $tickets = array_filter($tickets, fn($ticket) => $ticket['id'] != $id);
        file_put_contents(self::getTicketsFile(), json_encode(array_values($tickets), JSON_PRETTY_PRINT));
        return true;
    }

    // Ticket stats
    public static function stats(): array {
        $tickets = self::all();
        return [
            'total' => count($tickets),
            'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'in_progress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
            'closed' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed')),
        ];
    }
}
