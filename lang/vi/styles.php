<?php

return [
    'bold'          => "font-weight: bold;",
    // 'bold'          => "font-weight: 900;",
    'color'         => "color: :value;",
    'bg_color'      => "background-color: :value;",
    // 'stroke'        => "-webkit-text-stroke: 1px :value;",
    'stroke'        => "text-shadow:
        -1px -1px 0 :value,
        1px -1px 0 :value,
        -1px  1px 0 :value,
        1px  1px 0 :value;",
    'italic'        => 'font-style: italic;',
    'underline'     => 'text-decoration: underline;',
    'font'          => "font-family: :value, sans-serif;",
    'font_size'     => "font-size: :value:unit;",
    'width'         => "width: :value;",
    'height'        => "height: :value;",
    'top'           => 'top: :value:unit;',
    'left'          => 'left: :value:unit;',
    'text_align'    => 'text-align: :value;',
    'padding'       => 'padding: :top:unit :right:unit :bottom:unit :left:unit;',
    'border_radius' => 'border-radius: :value:unit;;',
];
