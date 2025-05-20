<?php

namespace app\models;

use app\models\base\DevelopmentPlan as DevelopmentPlanBase;

/**
 * Class DevelopmentPlan — extend your logic here.
 */
class DevelopmentPlan extends DevelopmentPlanBase
{
    // Example values prefix for conversion
    /**
     * @var array<string, string>
     */
    private static array $statusLabels = [
        'pending' => '⏳',
        'in_progress' => '🔧',
        'completed' => '✅',
    ];

    /**
     * Make status label with icon.
     * @param string $status
     * @return string
     */
    public static function makeStatusAdv(string $status): string
    {
        $advStatus = ucfirst(str_replace('_', ' ', $status));
        return (self::$statusLabels[$status] ?? '❓') . ' ' . t('app', $advStatus);
    }

    /**
     * Virtual field, calculated based on status.
     * @return string
     */
    public function getStatusAdv(): string
    {
        return self::makeStatusAdv($this->status);
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), [
            'status_adv',
        ]);
    }
}
