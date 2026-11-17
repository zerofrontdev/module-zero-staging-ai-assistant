<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 *
 * Zero_StagingAiAssistant: the AI Assistant's and the Claude connector's tools for Zero Staging
 * (list_campaigns, schedule_change).
 */
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Zero_StagingAiAssistant', __DIR__);
