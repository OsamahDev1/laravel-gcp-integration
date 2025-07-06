<?php

return [
    "default_namespace" => "App\\Integrations",
    "default_path" => "app/Integrations",
    "search_paths" => [
        "app/Integrations",
        "app/Services/Integration",
        "app/Services/Integrations",
    ],
    "defaults" => [
        "timeout" => 30,
        "retry_attempts" => 3,
        "retry_delay" => 5,
    ],
];
