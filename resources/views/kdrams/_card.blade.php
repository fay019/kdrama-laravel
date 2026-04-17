@props(['kdrama', 'filters' => [], 'userStatus' => null])

<x-drama-card
    :item="$kdrama"
    variant="default"
    :userStatus="$userStatus ?? null"
/>
