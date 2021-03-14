<?php
if(!function_exists('parse_padding')){
        function parse_padding($source)
        {
            $length  = strlen(strval(count($source['source']) + $source['first']));
            return 40 + ($length - 1) * 8;
        }
}

if(!function_exists('parse_class')){
    function parse_class($name)
    {
        $names = explode('\\', $name);
        return '<abbr title="'.$name.'">'.end($names).'</abbr>';
    }
}

if(!function_exists('parse_file')){
    function parse_file($file, $line)
    {
        return '<a class="toggle" title="'."{$file} line {$line}".'">'.basename($file)." line {$line}".'</a>';
    }
}

if(!function_exists('parse_args')){
    function parse_args($args)
    {
        $result = [];

        foreach ($args as $key => $item) {
            switch (true) {
                case is_object($item):
                    $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                break;
                case is_array($item):
                    if(count($item) > 3){
                    $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                    } else {
                    $value = sprintf('[%s]', parse_args($item));
                    }
                break;
                case is_string($item):
                    if(strlen($item) > 20){
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


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>出错啦</title>
    <style>
        body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td { margin:0; padding:0; box-sizing: border-box; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; -ms-box-sizing: border-box;}
        body { background:#fff; color:#555; font-size:14px; font-family: "Microsoft Yahei"; }
        a { color:#555; text-decoration:none; }
        a:hover { text-decoration:none; }
        img { border:none; }
        .clearfix:after, .clearfix:before {content: ""; display: block; height:0; clear:both; visibility: hidden;}
        .clearfix { *zoom:1; }
        .errPage{width:800px;margin:0 auto;}
        .errPage .header{margin-top:120px;}
        .errPage .header .left{width:250px;height:250px;float:left;}
        .errPage .header .left img{width:100%;height:100%;}
        .errPage .header .right{width:395px;float:right;margin-top:15px;margin-right: 50px;}
        .errPage .header .right .picture{width:228px;height:40px;}
        .errPage .header .right .picture img{width:100%;height:100%;}
        .errPage .header .right .title{font-size:20px;color:#979DAB;margin-top:26px;height:70px;}
        .errPage .header .right .question{margin-top:80px;}
        .errPage .header .right .question .btn{-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;user-select:none;display:inline-block;*display:inline;zoom:0;width:116px;height:28px;border-radius:18px;background-color:#999;font-size:12px;color:#fff;text-align:center;line-height:28px;cursor:pointer;}
        .errPage .header .right .question .btn.on{background-color:#1951FC;}
        .errPage .header .right .question .btn img{width:10px;height:6px;margin-left:10px;}
        .errPage .conter{width:800px;height:424px;padding:25px 29px;background-color:#F7F7F7;border-radius:18px;overflow:auto;font-size:14px;color:#666666;margin-top:55px;line-height:2.4;display:none;}
        .source-code pre {margin: 0;}
        .source-code pre li {height: 18px;line-height: 18px;}
        .source-code pre ol{margin: 0;color: #4288ce;display: inline-block;min-width: 100%;box-sizing: border-box;font-size:14px;font-family: "Century Gothic",Consolas,"Liberation Mono",Courier,Verdana;padding-left: <?php echo (isset($source) && !empty($source)) ? parse_padding($source) : 40;  ?>px;}
        .line-error{background: #f8cbcb;}
        /* SPAN elements with the classes below are added by prettyprint. */
        pre.prettyprint .pln { color: #000 }  /* plain text */
        pre.prettyprint .str { color: #080 }  /* string content */
        pre.prettyprint .kwd { color: #008 }  /* a keyword */
        pre.prettyprint .com { color: #800 }  /* a comment */
        pre.prettyprint .typ { color: #606 }  /* a type name */
        pre.prettyprint .lit { color: #066 }  /* a literal value */
        /* punctuation, lisp open bracket, lisp close bracket */
        pre.prettyprint .pun, pre.prettyprint .opn, pre.prettyprint .clo { color: #660 }
        pre.prettyprint .tag { color: #008 }  /* a markup tag name */
        pre.prettyprint .atn { color: #606 }  /* a markup attribute name */
        pre.prettyprint .atv { color: #080 }  /* a markup attribute value */
        pre.prettyprint .dec, pre.prettyprint .var { color: #606 }  /* a declaration; a variable name */
        pre.prettyprint .fun { color: red }  /* a function name */
        h2 {color: #4288ce;font-weight: 400;padding: 6px 0;margin: 6px 0 0;font-size: 14px;border-bottom: 1px solid #eee;}
        abbr {cursor: help;text-decoration: underline;text-decoration-style: dotted;}
        h1 {margin: 10px 0 0;font-size: 28px;font-weight: 500;line-height: 32px;}
        .source-code::-webkit-scrollbar{width:10px;height:10px;}
        .source-code::-webkit-scrollbar-track{border-radius:2px;}
        .source-code::-webkit-scrollbar-thumb{background: #bfbfbf;border-radius:10px;}
        .source-code::-webkit-scrollbar-thumb:hover{background: #333;}
    </style>
</head>
<body>
<div class="errPage">
    <div class="header">
        <div class="left"><img src="data:image/png;base64,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"></div>
        <div class="right">
            <div class="title">
                <div class="info">
                    <div>
                        <h2><?php echo nl2br(htmlentities($message)); ?></h2>
                    </div>
                    <div>
                        <h1 style="font-size: 14px;">[<?php echo $code; ?>]&nbsp;<?php echo isset($name) ? sprintf('%s in %s', parse_class($name), parse_file($file, $line)) : $exception->getMessage(); ?></h1>
                    </div>
                </div>
            </div>
            <div class="question">
                <div class="btn on"><a style="color: #ffffff" href="javascript:history.back(-1)">返回上一页</a></div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="conter source-code">
        <?php if(!empty($source)){?>
        <div class="source-code">
            <pre class="prettyprint lang-php"><ol start="<?php echo $source['first']; ?>"><?php foreach ((array) $source['source'] as $key => $value) { ?><li class="line-<?php echo $key + $source['first']; ?>"><code><?php echo htmlentities($value); ?></code></li><?php } ?></ol></pre>
        </div>
        <?php }?>
    </div>
</div>
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
<script>

    var LINE =<?php echo isset($line) ? $line : 0; ?>;
    var down = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAABuElEQVRoQ+2Y4SoEURiGn+8mXIkbcAuKkpKSlFBSkhIpSUmhLUlJbYqUW3ADfvLPVbiAV1uztYY1Z+Z8x1id+bez55x9nvc9u81ZY8QvG3F+skDbDeYGcgORCeQtFBlg9PTcQHSEkQv8vwYk7QJjQMfMXiMDcpsu6Rh4B7pm9tZf+FMDBfxe8eYLMP0XJCTd9VgKrkczmxwmoFJkrUtIugVmSlwTZvbUu1du4AxY/SsSkrrAbInn2czGv22gd1PSPTDVtoSkG2CuiuPbX6G2JSRdA/NV8F+20OCEtiQkXQELIfA/CrSxnSRdAouh8JUCvykh6QJYqgMfJPAbEpI6wHJd+GCBlBKSzoGVJvC1BFJISDoF1prC1xbwlJB0AqzHwDcS8JAoHsw2YuEbC8RISDoCNj3gowSaSEg6BLa84KMF6khIOgC2PeFdBEIkJO0DO97wbgIVEg9A75Q3eLmdM1zPxEMeAEvsuMG7NtCnrJBwhU8iEPKdKFcS89p1Cw2ClJpwT77/WckEiiaS/0WTVCBma4TOzQKhSaUalxtIlWzourmB0KRSjcsNpEo2dN3cQGhSqcaNfAMfxuz5MbcQ4poAAAAASUVORK5CYII=";
    var up = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAABtElEQVRoQ+2Y3SoFURiGv+dmHHABTt2AM1FSkkRKUlIi2SmpnRJKJElJbWduwCGX4U6Wds3sttkz3/x8a+2xa83hNGvN87zvrFnTIBN+MOH8EgXabjA2EBswJhAfIWOA5uGxAXOExgliA0UBOuemReRYRL6AG2PQhcODNJDA90RkJrnzO7AYQsK7QA58yh1EwquAAh9MwptABfggEl4EFPiOiCwMrQXvEmYBBf4MOAm9JkwCCtw5cJTGHVKisYACdQEcZl+ZoSQaCSgwXeCgZHMb3h/Ma6K2gAJ/CeyXbVa+m6gloNz8Ctgrgw+xJioLKPDXwG5VeN8SlQQU+Ftgpy68T4lSAQX+DthuCu9LQhVQ4O+BLSu8D4lCAQX+EdjwBW+VyBVQ4J+Add/wFokRAQX+GVgLBd9U4o+AAv8CrIaGbyKRFfgWkdkM6CuwMi74ChIPwGZ63UDAOTcnIp8Z0DdgedzwZRLAgDvbwIeIzCcT9ICltuAViQ5wOtJA/4RzbkpE+p8FP0C3bfiMRO4vmtKd+L9IFHFEgbYbig3EBowJxEfIGKB5eGzAHKFxgtiAMUDz8F/6UtIxXjMNGgAAAABJRU5ErkJggg==";
    $(document).ready(function(){

        $.getScript = function(src, func){
            var script = document.createElement('script');
            
            script.async  = 'async';
            script.src    = src;
            script.onload = func || function(){};
            
            $('head')[0].appendChild(script);
        }

        var k = true;
        /*$(".question .btn").on('click',function(){
            if(k){
                $('.conter').show();
                $(this).addClass('on');
                $(this).find('img').attr('src',down);
                k = false;
            }else {
                $('.conter').hide();
                $(this).removeClass('on');
                $(this).find('img').attr('src',up);
                k = true;
            }
       });*/
        var ol    = $('ol', $('.prettyprint')[0]);
        // 设置出错行
        var err_line = $('.line-' + LINE, ol[0])[0];
        if(err_line) err_line.className = err_line.className + ' line-error';
        $.getScript('//cdn.bootcss.com/prettify/r298/prettify.min.js', function(){
            prettyPrint();

            // 解决Firefox浏览器一个很诡异的问题
            // 当代码高亮后，ol的行号莫名其妙的错位
            // 但是只要刷新li里面的html重新渲染就没有问题了
            if(window.navigator.userAgent.indexOf('Firefox') >= 0){
                ol[0].innerHTML = ol[0].innerHTML;
            }
        });
    });
</script>
</body>
</html>