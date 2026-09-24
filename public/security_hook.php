<?php
// .claude/hooks/pre_tool_security.php
/**
 * Пре-хук безопасности: Блокировка доступа к чувствительным файлам и опасным командам
 * Адаптировано для WordPress проекта
 */

function validate_file_access($tool_input) {
    if (!isset($tool_input['file_path'])) {
        return true;
    }
    $file_path = $tool_input['file_path'];

    // Заблокированные паттерны
    $sensitive_patterns = [
        '/\.env$/i',
        '/\.env\./i',
        '/credentials/i',
        '/secrets/i',
        '/\.pem$/i',
        '/\.key$/i',
        '/id_rsa/i',
        '/\.ssh\//i',
        '/config\.json$/i',             // Если содержит секреты
        '/\.aws\/credentials/i',
        '/wp-config\.php$/i',           // WordPress Config (CRITICAL)
        '/auth\.json$/i',               // Composer Auth
        '/db_connect\.php$/i',          // Custom DB Connect script (Specific to this project)
    ];

    foreach ($sensitive_patterns as $pattern) {
        if (preg_match($pattern, $file_path)) {
            return false; // Заблокировать доступ
        }
    }

    return true; // Разрешить доступ
}

function validate_bash_command($tool_input) {
    if (!isset($tool_input['command'])) {
        return true;
    }
    $command = $tool_input['command'];

    // Опасные паттерны
    $dangerous_patterns = [
        '/rm\s+-rf\s+\//i',      # rm -rf /
        '/sudo\s+rm/i',          # sudo rm
        '/chmod\s+777/i',        # Слишком разрешительные права
        '/>\s*\/etc\//i',        # Запись в системные директории
        '/curl.*\|\s*bash/i',    # Pipe в bash (рискованно)
        '/eval\s+/i',            # Eval с недоверенным вводом
        '/dd\s+if=.*of=\/dev\//i', # Дисковые операции
    ];

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $command)) {
            return false; // Заблокировать команду
        }
    }

    return true; // Разрешить команду
}

function main() {
    // Чтение ввода инструмента из stdin
    $input_json = file_get_contents('php://stdin');
    if (!$input_json) {
        // Если нет ввода, считаем что всё ок или ошибка - но для хука лучше пропустить если контекст не ясен,
        // однако хук вызывается с аргументами. Предположим пустой ввод = ошибка 0 (нечего проверять).
        exit(0);
    }

    $input_data = json_decode($input_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        fwrite(STDERR, "Ошибка JSON: " . json_last_error_msg() . "\n");
        exit(2); // Ошибка парсинга - блокируем на всякий случай
    }

    $tool_name = isset($input_data['tool_name']) ? $input_data['tool_name'] : '';
    $tool_input = isset($input_data['tool_input']) ? $input_data['tool_input'] : [];

    // Валидация на основе типа инструмента
    if ($tool_name === 'Read' || $tool_name === 'view_file' || $tool_name === 'read_resource') {
        if (!validate_file_access($tool_input)) {
            fwrite(STDERR, "ЗАБЛОКИРОВАНО: Доступ к чувствительному файлу запрещён: " . ($tool_input['file_path'] ?? '') . "\n");
            exit(2); // Блокировка
        }
    } elseif ($tool_name === 'Bash' || $tool_name === 'run_command') {
        if (!validate_bash_command($tool_input)) {
            fwrite(STDERR, "ЗАБЛОКИРОВАНО: Опасная команда заблокирована: " . ($tool_input['command'] ?? '') . "\n");
            exit(2);
        }
    }

    // Если мы достигли этой точки, разрешить выполнение
    exit(0);
}

main();
