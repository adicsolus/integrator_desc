<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerGFEZ1rL\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerGFEZ1rL/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerGFEZ1rL.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerGFEZ1rL\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \ContainerGFEZ1rL\App_KernelDevDebugContainer([
    'container.build_hash' => 'GFEZ1rL',
    'container.build_id' => '37c53584',
    'container.build_time' => 1686410012,
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerGFEZ1rL');