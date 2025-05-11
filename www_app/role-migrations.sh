#!/bin/bash
php yii migrate --migrationPath=@yii/rbac/migrations

php yii rbac/init