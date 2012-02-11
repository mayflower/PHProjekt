function! RunNodecs()
    let l:filename=@%
    let l:nodecs_output=system('node ~/git/PHProjekt/jshint/hintcli.js '.l:filename)
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
