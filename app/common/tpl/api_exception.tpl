<?php
/** @var array $traces */
if (!function_exists('parse_padding')) {
    function parse_padding($source)
    {
        $length  = strlen(strval(count($source['source']) + $source['first']));
        return 40 + ($length - 1) * 8;
    }
}

if (!function_exists('parse_class')) {
    function parse_class($name)
    {
        $names = explode('\\', $name);
        return end($names);
    }
}

if (!function_exists('parse_file')) {
    function parse_file($file, $line)
    {
        return basename($file)." line {$line}";
    }
}

if (!function_exists('parse_args')) {
    function parse_args($args)
    {
        $result = [];
        foreach ($args as $key => $item) {
            switch (true) {
                case is_object($item):
                    $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                    break;
                case is_array($item):
                    if (count($item) > 3) {
                        $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                    } else {
                        $value = sprintf('[%s]', parse_args($item));
                    }
                    break;
                case is_string($item):
                    if (strlen($item) > 20) {
                        $value = sprintf(
                            '\'<a class="toggle" title="%s">%s...</a>\'',
                            htmlentities($item),
                            htmlentities(substr($item, 0, 20))
                        );
                    } else {
                        $value = sprintf("'%s'", htmlentities($item));
                    }
                    break;
                case is_int($item):
                case is_float($item):
                    $value = $item;
                    break;
                case is_null($item):
                    $value = '<em>null</em>';
                    break;
                case is_bool($item):
                    $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                    break;
                case is_resource($item):
                    $value = '<em>resource</em>';
                    break;
                default:
                    $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                    break;
            }

            $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
        }

        return implode(', ', $result);
    }
}
if (!function_exists('echo_value')) {
    function echo_value($val)
    {
        if (is_array($val) || is_object($val)) {
            echo htmlentities(json_encode($val, JSON_PRETTY_PRINT));
        } elseif (is_bool($val)) {
            echo $val ? 'true' : 'false';
        } elseif (is_scalar($val)) {
            echo htmlentities($val);
        } else {
            echo 'Resource';
        }
    }
}
?>
    <?php if (\think\facade\App::isDebug()) { ?>
        <?php foreach ($traces as $index => $trace) { ?>
        <?php echo "#{$index} [{$trace['code']}]" . sprintf('%s in %s', parse_class($trace['name']), parse_file($trace['file'], $trace['line'])); ?>
        <?php echo nl2br(htmlentities($trace['message'])); ?>
            <?php if (!empty($trace['source'])) { ?>
                start="<?php echo $trace['source']['first']; ?>"
                <?php foreach ((array) $trace['source']['source'] as $key => $value) { ?>
line-<?php echo "{$index}-"; echo $key + $trace['source']['first']; echo $trace['line'] === $key + $trace['source']['first'] ? ' line-error' : ''; ?>
<?php echo htmlentities($value); ?><?php } ?>
            <?php }?>
        <div class="trace">
            <h2 data-expand="<?php echo 0 === $index ? '1' : '0'; ?>">Call Stack</h2>
            <ol>
                <li><?php echo sprintf('in %s', parse_file($trace['file'], $trace['line'])); ?></li>
                <?php foreach ((array) $trace['trace'] as $value) { ?>
                    <li>
                        <?php
                        // Show Function
                        if ($value['function']) {
                            echo sprintf(
                                'at %s%s%s(%s)',
                                isset($value['class']) ? parse_class($value['class']) : '',
                                isset($value['type'])  ? $value['type'] : '',
                                $value['function'],
                                isset($value['args'])?parse_args($value['args']):''
                            );
                        }

                        // Show line
                        if (isset($value['file']) && isset($value['line'])) {
                            echo sprintf(' in %s', parse_file($value['file'], $value['line']));
                        }
                        ?>
                    </li>
                <?php } ?>
            </ol>
        </div>
    </div>
        <?php } ?>
    <?php } else { ?>
    <?php echo htmlentities($message); ?>
    <?php } ?>

    <?php if (!empty($datas)) { ?>
    <div class="exception-var">
        <h2>Exception Datas</h2>
        <?php foreach ((array) $datas as $label => $value) { ?>
        <table>
            <?php if (empty($value)) { ?>
            <caption><?php echo $label; ?><small>empty</small></caption>
            <?php } else { ?>
            <caption><?php echo $label; ?></caption>
            <tbody>
                <?php foreach ((array) $value as $key => $val) { ?>
                <tr>
                    <td><?php echo htmlentities($key); ?></td>
                    <td><?php echo_value($val); ?></td>
                </tr>
                <?php } ?>
            </tbody>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($tables)) { ?>
    <div class="exception-var">
        <h2>Environment Variables</h2>
        <?php foreach ((array) $tables as $label => $value) { ?>
        <table>
            <?php if (empty($value)) { ?>
            <caption><?php echo $label; ?><small>empty</small></caption>
            <?php } else { ?>
            <caption><?php echo $label; ?></caption>
            <tbody>
                <?php foreach ((array) $value as $key => $val) { ?>
                <tr>
                    <td><?php echo htmlentities($key); ?></td>
                    <td><?php echo_value($val); ?></td>
                </tr>
                <?php } ?>
            </tbody>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
    <?php } ?>
    <?php if (\think\facade\App::isDebug()) { ?>
    <?php } ?>