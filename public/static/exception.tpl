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
        .errPage .header{margin-top:160px;}
        .errPage .header .left{width:290px;height:290px;float:left;}
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
        <div class="left"><img src="data:image/jpg;base64,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"></div>
        <div class="right">
            <div class="picture"></div>
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