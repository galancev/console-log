<?php

namespace Galantcev\Components;

/**
 * Логирование в буфер и одновременный вывод в консоль
 * Class ConsoleLog
 * @package Galantcev\Components
 */
class ConsoleLog
{
    /**
     * Использовать ли таймштампы при выводе
     * @var bool
     */
    private $useTimestamps = true;

    /**
     * Формат вывода даты и времени
     * @var string
     */
    private $timeFormat = 'd-m-Y H:i:s';

    /**
     * Использовать цветные метки (только в консоль)
     * @var bool
     */
    private $echoLabels = true;

    /**
     * Сюда храним консольный вывод
     * @var string
     */
    protected $output = '';

    /**
     * Сюда храним консольный вывод для вывода
     * @var string
     */
    protected $outputForErrors = '';

    /**
     * Использовать вывод в консоль
     * @var bool
     */
    protected $outputToConsole = true;

    /**
     * Использовать вывод в консоль только если была ошибка в процессе
     * @var bool
     */
    protected $outputOnlyIfError = false;

    /**
     * Использовать вывод в буфер
     * @var bool
     */
    protected $outputToBuffer = false;

    /**
     * Коллбек для отработки вывода в файл
     * @var \Closure
     */
    protected $callback;

    /**
     * Устанавливает вывод временных меток перед выводом
     * @param bool $using
     */
    public function setUseTimestamps($using = true)
    {
        $this->useTimestamps = (bool)$using;
    }

    /**
     * Устанавливает формат временной метки
     * @param $stringFormat
     */
    public function setTimeFormat($stringFormat)
    {
        $this->timeFormat = $stringFormat;
    }

    /**
     * Устанавливает формат временной метки
     * @param $show
     */
    public function setShowLabels($show = true)
    {
        $this->echoLabels = (bool)$show;
    }

    /**
     * Устанавливает необходимость вывода в консоль
     * @param bool $flag
     */
    public function setOutputToConsole($flag)
    {
        $this->outputToConsole = (bool)$flag;
    }

    /**
     * Устанавливает необходимость вывода в буфер
     * @param bool $flag
     */
    public function setOutputToBuffer($flag)
    {
        $this->outputToBuffer = (bool)$flag;
    }

    /**
     * Устанавливает вывод ошибок только если были ошибки
     * @param bool $flag
     */
    public function setOutputOnlyIfError($flag)
    {
        $this->outputOnlyIfError = (bool)$flag;
    }

    /**
     * Выводить простой текст
     * @param string $str Выводимый текст
     * @param string $endString Конец текст (по умолчанию перевод строки)
     */
    public function text($str, $endString = PHP_EOL)
    {
        $str = (string)$str;
        $str = $str . $endString;

        if ($this->outputToConsole) {
            $outputStr = $this->addTime($str);
            $this->output($outputStr);
        }

        if ($this->outputToBuffer) {
            $saveStr = $this->addTime($str, false);
            $this->addBuffer($saveStr);
        }
    }

    /**
     * Выводит ошибку
     * @param $str
     * @param string $endString Конец текст (по умолчанию перевод строки)
     */
    public function error($str, $endString = PHP_EOL)
    {
        $str = (string)$str;
        $str = $str . $endString;

        if ($this->outputToConsole) {
            if($this->outputOnlyIfError)
                $this->outputIfErrors();

            $outputStr = $this->addLabel($str, 'ERROR', 101);
            $outputStr = $this->addTime($outputStr);
            $this->output($outputStr);
        }

        if ($this->outputToBuffer) {
            $saveStr = $this->addLabel($str, 'ERROR', 101, 37, false);
            $saveStr = $this->addTime($saveStr, false);
            $this->addBuffer($saveStr);
        }
    }

    /**
     * Выводит варнинг
     * @param $str
     * @param string $endString Конец текст (по умолчанию перевод строки)
     */
    public function warning($str, $endString = PHP_EOL)
    {
        $str = (string)$str;
        $str = $str . $endString;

        if ($this->outputToConsole) {
            $outputStr = $this->addLabel($str, 'WARNING', 103, 30);
            $outputStr = $this->addTime($outputStr);
            $this->output($outputStr);
        }

        if ($this->outputToBuffer) {
            $saveStr = $this->addLabel($str, 'WARNING', 103, 30, false);
            $saveStr = $this->addTime($saveStr, false);
            $this->addBuffer($saveStr);
        }
    }

    /**
     * Выводит сообщение успешной операции
     * @param $str
     * @param string $endString Конец текст (по умолчанию перевод строки)
     */
    public function success($str, $endString = PHP_EOL)
    {
        $str = (string)$str;
        $str = $str . $endString;

        if ($this->outputToConsole) {
            $outputStr = $this->addLabel($str, 'SUCCESS', 102, 30);
            $outputStr = $this->addTime($outputStr);
            $this->output($outputStr);
        }

        if ($this->outputToBuffer) {
            $saveStr = $this->addLabel($str, 'SUCCESS', 102, 30, false);
            $saveStr = $this->addTime($saveStr, false);
            $this->addBuffer($saveStr);
        }
    }

    /**
     * Добавляем в консольный вывод нечто новое
     * И вызываем коллбек для записи в лог, если он был настроен
     * @param $text
     */
    private function addBuffer($text)
    {
        $this->output .= $text;

        if ($this->callback)
            $this->callback->__invoke($text);
    }

    /**
     * Добавляем в консольный вывод нечто новое
     * @param $text
     */
    private function addErrorBuffer($text)
    {
        $this->outputForErrors .= $text;
    }

    /**
     * Очищаем буфер консольного вывода
     */
    public function clear()
    {
        $this->output = '';
    }

    /**
     * Возвращает буфер консольного вывода
     * @return string
     */
    public function get()
    {
        return $this->output;
    }

    /**
     * Сохраняет в файл сохранённый вывод
     * @param $fileName
     * @param bool $append
     * @return bool|int
     */
    public function save($fileName, $append = false)
    {
        $appendFlag = 0;
        if ($append)
            $appendFlag = FILE_APPEND;

        return file_put_contents($fileName, $this->get(), $appendFlag);
    }

    /**
     * Выводит текст на экран
     * @param $str
     */
    private function output($str)
    {
        if (!php_sapi_name() == 'cli')
            return;

        if ($this->outputOnlyIfError) {
            $this->addErrorBuffer($str);

            return;
        }

        echo $str;
    }

    /**
     * Выводит буфер в случае ошибки и выключает буферизирование
     */
    private function outputIfErrors()
    {
        $this->outputOnlyIfError = false;

        if (!php_sapi_name() == 'cli')
            return;

        echo $this->outputForErrors;

        $this->outputForErrors = '';
    }

    /**
     * Возвращает текст уже с добавленным таймштампом
     * @param string $str
     * @param bool $colorize
     * @return string
     */
    private function addTime($str, $colorize = true)
    {
        if ($this->useTimestamps) {
            $time = date($this->timeFormat);

            if ($colorize) {
                $str = "\e[1;37m[\e[0;37m{$time}\e[1;37m]\e[0m {$str}";
            } else {
                $str = "[{$time}] {$str}";
            }
        }

        return $str;
    }

    /**
     * Добавляет метку в строку
     * @param $str
     * @param $label
     * @param $bg
     * @param int $color
     * @param bool $colorize
     * @return string
     */
    private function addLabel($str, $label, $bg, $color = 37, $colorize = true)
    {
        if ($this->echoLabels) {
            if ($colorize) {
                $str = "\e[0;{$bg}m\e[{$color}m {$label} \e[0m {$str}";
            } else {
                $str = "[{$label}] {$str}";
            }
        }

        return $str;
    }

    /**
     * Дампит переменную
     * @param $var
     */
    public function dump($var)
    {
        $str = "";

        $str = $this->addLabel($str, 'DUMP', 107, 30);
        if (is_string($var) || is_bool($var) || is_object($var) || is_null($var)) {
            ob_start();
            var_export($var);

            $dump = ob_get_contents();
            ob_end_clean();

            $str .= ($this->echoLabels ? PHP_EOL : '') . "\e[0;37m{$dump}\e[0m" . PHP_EOL;
        } else {
            $str .= ($this->echoLabels ? PHP_EOL : '') . "\e[0;37m" . print_r($var, 1) . "\e[0m" . PHP_EOL;
        }

        $this->output($str);
    }

    /**
     * Устанавливает коллбек для добавления записи в лог
     * @param \Closure $callback
     */
    public function setLogCallback($callback)
    {
        $this->callback = $callback;
    }
}
