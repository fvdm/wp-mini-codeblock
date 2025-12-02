(function() {
    'use strict';

    // Reusable pattern fragments
    var STR = /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g;
    var MSTR = /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|`(?:\\.|[^`\\])*`)/g;
    var MLCMT = /(\/\*[\s\S]*?\*\/)/g;
    var NUM = /\b(\d+\.?\d*|\.\d+)\b/g;
    var NUMHEX = /\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b/g;

    // Lightweight tokenizer patterns
    var patterns = {
        c: [
            { r: /(\/\/.*$)/gm, c: 'comment' },
            { r: MLCMT, c: 'comment' },
            { r: STR, c: 'string' },
            { r: /\b(int|char|float|double|void|long|short|signed|unsigned|struct|union|enum|typedef|sizeof|if|else|for|while|do|switch|case|default|break|continue|return|goto|const|static|extern|auto|register|volatile|inline|restrict)\b/g, c: 'keyword' },
            { r: /#\s*(include|define|ifdef|ifndef|endif|pragma)/g, c: 'preprocessor' },
            { r: NUMHEX, c: 'number' }
        ],
        css: [
            { r: MLCMT, c: 'comment' },
            { r: /([.#][a-zA-Z][a-zA-Z0-9_-]*)/g, c: 'selector' },
            { r: /([a-zA-Z-]+)\s*:/g, c: 'property' },
            { r: STR, c: 'string' },
            { r: /#[0-9a-fA-F]{3,8}\b/g, c: 'number' },
            { r: /\b(\d+(?:px|em|rem|%|vh|vw|pt)?)/g, c: 'number' }
        ],
        html: [
            { r: /(<!--[\s\S]*?-->)/g, c: 'comment' },
            { r: /(<\/?[a-zA-Z][a-zA-Z0-9]*)/g, c: 'tag' },
            { r: /([a-zA-Z-]+)=/g, c: 'attr' },
            { r: STR, c: 'string' }
        ],
        ini: [
            { r: /(;.*$|#.*$)/gm, c: 'comment' },
            { r: /(\[[^\]]+\])/g, c: 'section' },
            { r: /^([a-zA-Z_][a-zA-Z0-9_.]*)(?=\s*=)/gm, c: 'property' },
            { r: STR, c: 'string' }
        ],
        javascript: [
            { r: /([^:]\/\/.*$)/gm, c: 'comment' },
            { r: MLCMT, c: 'comment' },
            { r: MSTR, c: 'string' },
            { r: /[^'"]\b([a-zA-Z0-9_]+)\s*:/g, c: 'property' },
            { r: /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|try|catch|throw|typeof|instanceof|delete|void|yield|break|continue|switch|case|default|do)\b/g, c: 'keyword' },
            { r: /\b(true|false|null|undefined|NaN|Infinity)\b/g, c: 'literal' },
            { r: NUM, c: 'number' },
            { r: /([a-zA-Z_$][a-zA-Z0-9_$]*)\s*(?=\()/g, c: 'function' }
        ],
        json: [
            { r: /"(?:\\.|[^"\\])*"/g, c: 'string' },
            { r: /\b(true|false|null)\b/g, c: 'literal' },
            { r: /\b(-?\d+\.?\d*|\.\d+)\b/g, c: 'number' }
        ],
        lua: [
            { r: /(--.*$)/gm, c: 'comment' },
            { r: /(--\[\[[\s\S]*?\]\])/g, c: 'comment' },
            { r: /("([^"\\]|\\.)*"|'([^'\\]|\\.)*'|\[\[[\s\S]*?\]\])/g, c: 'string' },
            { r: /\b(and|break|do|else|elseif|end|false|for|function|goto|if|in|local|nil|not|or|repeat|return|then|true|until|while)\b/g, c: 'keyword' },
            { r: /\b(print|pairs|ipairs|next|type|tostring|tonumber|error|assert|pcall|xpcall|require|dofile|load|loadfile|set)\b/g, c: 'function' },
            { r: /\b(string|table|math|os|io|coroutine|debug|package)\b/g, c: 'type' },
            { r: NUMHEX, c: 'number' },
            { r: /\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?=\s*[,)=])/g, c: 'variable' },
            { r: /(\.\.\.|\.{2}|[+\-*\/%^#=<>~]+)/g, c: 'operator' }
        ],
        php: [
            { r: /(#.*$|\/\/.*$)/gm, c: 'comment' },
            { r: MLCMT, c: 'comment' },
            { r: STR, c: 'string' },
            { r: /\b(class|function|public|private|protected|static|const|return|if|else|foreach|while|new|namespace|use|trait|interface|extends|implements|echo|print|require|include)\b/g, c: 'keyword' },
            { r: /(\$[a-zA-Z_][a-zA-Z0-9_]*)/g, c: 'variable' },
            { r: NUM, c: 'number' }
        ],
        python: [
            { r: /(#.*$)/gm, c: 'comment' },
            { r: /('''[\s\S]*?'''|"""[\s\S]*?""")/g, c: 'string' },
            { r: STR, c: 'string' },
            { r: /\b(def|class|import|from|return|if|elif|else|for|while|in|try|except|finally|with|as|lambda|yield|async|await|pass|break|continue|global|nonlocal)\b/g, c: 'keyword' },
            { r: /\b(True|False|None)\b/g, c: 'literal' },
            { r: NUM, c: 'number' }
        ],
        shell: [
            { r: /(#.*$)/gm, c: 'comment' },
            { r: STR, c: 'string' },
            { r: /^(#!.*)$/gm, c: 'preprocessor' },
            { r: /\b(if|then|else|elif|fi|for|while|until|do|done|case|esac|select|function|in)\b/g, c: 'keyword' },
            { r: /\b(echo|printf|read|cd|rm|exit|return|source|alias|unalias|export|unset|shift|getopts|test|sudo( [A-Za-z0-9_./-]+)?|chmod|chown|\[\])\b/g, c: 'builtin' },
            { r: /\b(ls|cat|grep|awk|sed|find|xargs|tail|head|screen|dtach|curl|wget|ssh|scp|tar|zip|unzip|make|defaults|docker|kubectl|tmutil|pbcopy|pbpaste|node|npm)\b/g, c: 'function' },
            { r: /(\$[a-zA-Z_][a-zA-Z0-9_]*|\${[^}]+})/g, c: 'variable' },
            { r: /\s(--[a-zA-Z0-9\-]+|\-[a-zA-Z]+)/g, c: 'flag' },
            { r: /(\|\||&&|(?<!&[a-z]{2,5});|\||>>|>|<|2>>?|&>)/g, c: 'operator' },
            { r: /(\$\(|\)|`|\\\()/g, c: 'operator' },
            { r: /\b(\d+)\b/g, c: 'number' }
        ],
        text: [],
        typescript: [
            { r: /(\/\/.*$)/gm, c: 'comment' },
            { r: MLCMT, c: 'comment' },
            { r: MSTR, c: 'string' },
            { r: /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|interface|type|enum|namespace|public|private|protected|readonly|implements|declare)\b/g, c: 'keyword' },
            { r: /\b(string|number|boolean|any|void|never|unknown)\b/g, c: 'type' },
            { r: /\b(true|false|null|undefined|NaN|Infinity)\b/g, c: 'literal' },
            { r: NUM, c: 'number' }
        ],
        url: [
            { r: /\b([a-zA-Z][a-zA-Z0-9+.-]*:)(?=\/\/)/g, c: 'url-protocol' },
            { r: /\/\//g, c: 'url-slash' },
            { r: /(?<=\/\/)([^\s\/:?#]+)/g, c: 'url-host' },
            { r: /(:\d{2,5})(?=\/|[?#]|\s|$)/g, c: 'url-port' },
            { r: /(?<=\/)([^\/\s?#]+)(?=(\/|\?|#|$))/g, c: 'url-path' },
            { r: /\//g, c: 'url-slash' },
            { r: /(\?[^#\s]*)/g, c: 'url-query' },
            { r: /(#[^\s]*)/g, c: 'url-hash' }
        ],
        xml: [
            { r: /(<!--[\s\S]*?-->)/g, c: 'comment' },
            { r: /(<\/?[a-zA-Z][a-zA-Z0-9:]*)/g, c: 'tag' },
            { r: /([a-zA-Z:][a-zA-Z0-9:_-]*)=/g, c: 'attr' },
            { r: STR, c: 'string' }
        ]
    };

    // Aliases
    patterns.bash = patterns.shell;
    patterns.plain = patterns.text;

    // Highlight URL query parameters
    function highlightQuery(q) {
        return q.replace(/^\?(.*)$/, function(_, rest) {
            var out = '<span class="fmc-url-query-mark">?</span>';
            if (!rest) return out;

            var parts = rest.split(/(&amp;)/);
            for (var i = 0; i < parts.length; i++) {
                var part = parts[i];
                if (part === '&amp;') {
                    out += '<span class="fmc-url-query-amp">&amp;</span>';
                } else if (part) {
                    var m = part.match(/^([^=]+)(=(.*))?$/);
                    if (m) {
                        out += '<span class="fmc-url-query-key">' + m[1] + '</span>';
                        if (m[2]) out += '<span class="fmc-url-query-eq">=</span>';
                        if (m[3]) out += '<span class="fmc-url-query-value">' + m[3] + '</span>';
                    } else {
                        out += part;
                    }
                }
            }
            return out;
        });
    }

    // Escape HTML entities
    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Apply syntax highlighting to code
    function highlightCode(code, lang) {
        if (!patterns[lang]) lang = 'text';

        var html = escapeHtml(code);
        var tokens = [];

        patterns[lang].forEach(function(p) {
            html = html.replace(p.r, function(match) {
                var id = '\x00' + tokens.length + '\x00';
                tokens.push({ m: match, c: p.c });
                return id;
            });
        });

        for (var i = 0; i < tokens.length; i++) {
            var t = tokens[i];
            var inner = (lang === 'url' && t.c === 'url-query') ? highlightQuery(t.m) : t.m;
            html = html.split('\x00' + i + '\x00').join('<span class="fmc-' + t.c + '">' + inner + '</span>');
        }

        // Safety pass for any remaining placeholders
        html = html.replace(/\x00(\d+)\x00/g, function(_, idx) {
            var t = tokens[Number(idx)];
            return t ? t.m : '';
        });

        return html;
    }

    // Add scroll hint classes to wrappers
    function attachScrollHints() {
        var wrappers = document.querySelectorAll('.fmc-wrapper');
        for (var i = 0; i < wrappers.length; i++) {
            (function(wrap) {
                var pre = wrap.querySelector('.fmc-pre');
                if (!pre) return;

                var update = function() {
                    wrap.classList.toggle('fmc-wrapper--left', pre.scrollLeft > 0);
                };

                pre.addEventListener('scroll', update, { passive: true });
                update();
            })(wrappers[i]);
        }
    }

    // Initialize highlighting and copy buttons
    function init() {
        var codeBlocks = document.querySelectorAll('.fmc-code');
        for (var i = 0; i < codeBlocks.length; i++) {
            var el = codeBlocks[i];
            var lang = el.getAttribute('data-lang') || 'javascript';
            el.innerHTML = highlightCode(el.textContent, lang);
        }

        var copyBtns = document.querySelectorAll('.fmc-copy');
        for (var j = 0; j < copyBtns.length; j++) {
            copyBtns[j].addEventListener('click', function() {
                var btn = this;
                var wrapper = btn.closest('.fmc-wrapper');
                var code = wrapper.querySelector('.fmc-code').textContent;
                var trackId = btn.getAttribute('data-id') || 'fmc';

                navigator.clipboard.writeText(code).then(function() {
                    var original = btn.textContent;
                    btn.textContent = 'Copied!';
                    setTimeout(function() { btn.textContent = original; }, 2000);
                });

                // Optional Matomo tracking
                if (typeof Matomo !== 'undefined') {
                    try {
                        Matomo.getAsyncTracker().trackEvent('copy', 'fmc-code', trackId);
                    } catch (e) {}
                }
            });
        }

        attachScrollHints();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
