if exists('loaded_phprojekt_jshint_nodecs')
    finish
endif
let loaded_phprojekt_jshint_nodecs = 1

if !executable('node')
    finish
endif

let s:nodecs_command='node ' . expand("<sfile>:p:h") . '/hintcli.js '

function! RunNodecs()
    let l:filename=@%
    let l:nodecs_output=system(s:nodecs_command . l:filename)
    let l:nodecs_list=split(l:nodecs_output, "\n")
    cexpr l:nodecs_list
    cwindow
    :if len(l:nodecs_list) > 0
    :   cr
    :endif
endfunction

set errorformat+=\"%f\"\\,%l\\,%c\\,\"%m\"
"maybe helpful:
"command! Nodecs execute RunNodecs()
"autocmd BufWritePost *.js call RunNodecs()
