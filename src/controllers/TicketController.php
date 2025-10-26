<?php
namespace Kelvin\TwigApp\Controllers;

class TicketController {

    private static function getTicketsFile(): string {
        return __DIR__ . '/../../data/tickets.json';
    }

    // Fetch all tickets
    public static function all(): array {
        $tickets = json_decode(file_get_contents(self::getTicketsFile()), true) ?? [];
        return $tickets;
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
        file_put_contents(self::getTicketsFile(), json_encode($tickets));
        return $newTicket;
    }

    // Update a ticket by ID
    public static function update($id, array $data): ?array {
        $tickets = self::all();
        foreach ($tickets as $index => $ticket) {
            if ($ticket['id'] == $id) {
                $tickets[$index] = array_merge($ticket, $data, ['updatedAt' => time()]);
                file_put_contents(self::getTicketsFile(), json_encode($tickets));
                return $tickets[$index];
            }
        }
        return null;
    }

    // Delete a ticket by ID
    public static function delete($id): bool {
        $tickets = self::all();
        $tickets = array_filter($tickets, fn($ticket) => $ticket['id'] != $id);
        file_put_contents(self::getTicketsFile(), json_encode(array_values($tickets)));
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
