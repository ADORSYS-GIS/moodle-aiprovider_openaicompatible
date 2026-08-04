<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiprovider_openaicompatible\test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Trait for test cases.
 *
 * @package    aiprovider_openaicompatible
 * @copyright  2025   Adorsys GIS <gis-udm@adorsys.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait testcase_helper_trait {

    /**
     * Create the provider object.
     *
     * @param string $actionclass The action class to use.
     * @param array $actionconfig The action configuration to use.
     */
    public function create_provider(
        string $actionclass,
        array $actionconfig = [],
    ): \core_ai\provider {
        $manager = \core\di::get(\core_ai\manager::class);
        $config = [
            'apikey' => '123',
            'enableuserratelimit' => true,
            'userratelimit' => 1,
            'enableglobalratelimit' => true,
            'globalratelimit' => 1,
        ];
        $defaultactionconfig = [
            $actionclass => [
                'settings' => [
                    'model' => 'gpt-4o',
                    'endpoint' => "https://api.openai.com/v1/chat/completions",
                ],
            ],
        ];
        foreach ($actionconfig as $key => $value) {
            $defaultactionconfig[$actionclass]['settings'][$key] = $value;
        }
        $provider = $manager->create_provider_instance(
            classname: '\aiprovider_openaicompatible\provider',
            name: 'dummy',
            config: $config,
            actionconfig: $defaultactionconfig,
        );

        return $provider;
    }

    /**
     * Get a mocked HTTP client and inject it into the DI container.
     *
     * Returns an associative array with:
     *   - 'mock'   => the MockHandler (use ->append() to queue responses)
     *   - 'client' => the Guzzle Client configured with the mock handler
     *
     * @param array|null &$history Optional history array to capture requests.
     * @return array{mock: MockHandler, client: Client}
     */
    public function get_plugin_mocked_http_client(?array &$history = null): array {
        $mock = new MockHandler();
        $handlerstack = HandlerStack::create($mock);
        $client = new \core\http_client(['handler' => $handlerstack]);

        // Swap the http_client binding in the DI container so the plugin uses our mock.
        \core\di::get(\DI\Container::class)->set(\core\http_client::class, $client);

        return ['mock' => $mock, 'client' => $client];
    }
}
