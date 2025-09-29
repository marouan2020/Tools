<?php

namespace Analgo\Tools;

class Tracker {

    /**
     * Write visitor tracking data to a JSON file.
     *
     * This function processes a visitor's tracking information and stores it
     * in a JSON file named after the visitor ID. It handles page view counts,
     * updates existing entries, and ensures the file structure is consistent.
     *
     * @param string $inputJSON
     *   The raw JSON string containing visitor and page information. Expected structure:
     *   [
     *     "visitor" => ["id" => "visitor123", ...],
     *     "account" => ["id" => 208, "email" => "example@example.com"],
     *     "pages" => [
     *       "url" => "https://example.com/page",
     *       "title" => "Page Title"
     *     ]
     *   ]
     * @param string $path
     *   The directory path where the JSON file should be stored.
     *
     * @return array
     *   An associative array with:
     *   - 'status': HTTP-like status code (200 for success, 400 for invalid JSON)
     *   - 'received': The full data written to the file
     *   - 'file_path': The path to the JSON file created/updated
     *
     * Behavior:
     * - Adds the visitor's IP address automatically from $_SERVER['REMOTE_ADDR'].
     * - Sanitizes visitor ID to use as a filename (alphanumeric, _ and - only).
     * - Creates the directory if it does not exist.
     * - Initializes nbView counter for the current page to 1.
     * - If the file exists:
     *     - If the page URL already exists, increments nbView.
     *     - Otherwise, appends the new page to the pages array.
     * - If the file does not exist, creates a new file with the visitor's info.
     * - Writes the data back to the JSON file with pretty print.
     * - Logs the received input to PHP's error log.
     *
     * Example usage:
     * $tracker = new Tracker();
     * $response = $tracker->write($jsonInput, '/path/to/visitors');
     * echo $response['status']; // 200
     * echo $response['file_path']; // /path/to/visitors/visitor123.json
     */
    public function writeTrackingToJson(string $inputJSON, string $path): array {
        $input = json_decode($inputJSON, true);
        if(!$input){
            return ['status' => 400, 'message' => 'Invalid JSON'];
        }
        $input['ip'] = $_SERVER['REMOTE_ADDR'];
        $visitorId = preg_replace('/[^a-zA-Z0-9_-]/', '', $input['visitor']['id']);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $filePath = $path . '/' . $visitorId . '.json';
        $input['pages']['nbView'] = 1;
        $pages = $input['pages'];
        if (file_exists($filePath)) {
            $existingData = json_decode(file_get_contents($filePath), true) ?: [];
            $urlsPages = array_column($existingData['pages'], 'url');
            if (in_array($pages['url'], $urlsPages)) {
                foreach ($existingData['pages'] as $i => $pagedat) {
                    if ($pagedat['url'] === $pages['url']) {
                        $existingData['pages'][$i]['nbView'] = $pagedat['nbView'] + 1;
                    }
                }
            } else {
                $existingData['pages'][] = $pages;
            }
        } else {
            unset($input['pages']);
            $input['pages'][] = $pages;
            $existingData = $input;
        }
        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
        error_log('Received track event: ' . print_r($input, true));

        return [
            'status' => 200,
            'received' => $existingData,
            'file_path' => $filePath
        ];
    }
}