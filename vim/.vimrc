filetype off
set rtp+=~/.vim/bundle/Vundle.vim
call vundle#begin()


" php
"
Plugin 'shawncplus/phpcomplete.vim'

" golang

Plugin 'fatih/vim-go'
Plugin 'vim-jp/vim-go-extra'
" Plugin 'SirVer/ultisnips'
Plugin 'jnurmine/Zenburn'
Plugin 'Blackrush/vim-gocode'
Plugin 'Shougo/neocomplete.vim'
" django template
" Plugin 'kana/vim-textobj-user'
" Plugin 'mjbrownie/django-template-textobjects'

" Plugin 'Valloric/YouCompleteMe'
" Plugin 'tweekmonster/django-plus.vim'


" common
Bundle 'majutsushi/tagbar'
Plugin 'preservim/nerdtree'
Plugin 'ctrlpvim/ctrlp.vim'


"
" color
"
Plugin 'fatih/molokai'

call vundle#end()
filetype plugin indent on

" Brief help
" :PluginList       - lists configured plugins
" :PluginInstall    - installs plugins; append `!` to update or just
" :PluginUpdate
" :PluginSearch foo - searches for foo; append `!` to refresh local cache
" :PluginClean      - confirms removal of unused plugins; append `!` to
"  auto-approve removal
"
syntax on
set nocompatible
set backspace=indent,eol,start 
set ts=4 
set expandtab
let g:tagbar_width=50
nmap <F8> :TagbarToggle<CR>
imap <F6> <C-x><C-o>
" nnoremap <leader>n :NERDTreeFocus<CR>
" nnoremap <C-n> :NERDTree<CR>
" nnoremap <C-t> :NERDTreeToggle<CR>
map <F2> :NERDTreeToggle<CR>
autocmd bufenter * if (winnr("$") == 1 && exists("b:NERDTreeType") && b:NERDTreeType == "primary") | q | endif

let g:go_highlight_types = 1
let g:go_highlight_fields = 1
let g:go_highlight_functions = 1
" let g:go_highlight_function_calls = 1
let g:go_highlight_operators = 1
let g:go_highlight_extra_types = 1
let g:go_highlight_build_constraints = 1

" for fatih/molokia
let g:rehash256 = 1
let g:molokai_original = 1

au BufRead,BufNewFile *.html set filetype=gohtmltmpl
let g:neocomplete#enable_at_startup = 1

set smartindent
set softtabstop=4
set encoding=utf-8
set fileformats=unix,dos
set ignorecase
set smartcase
set showmatch
set expandtab
set tabstop=4
set shiftwidth=4
" set cursorline
