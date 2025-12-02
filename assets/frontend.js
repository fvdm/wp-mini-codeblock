(function() {
    'use strict';

    // Lightweight tokenizer patterns
    const patterns = {
        c: [
            { r: /(\/\/.*$)/gm, c: 'comment' },
            { r: /(\/\*[\s\S]*?\*\/)/g, c: 'comment' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' },
            { r: /\b(int|char|float|double|void|long|short|signed|unsigned|struct|union|enum|typedef|sizeof|if|else|for|while|do|switch|case|default|break|continue|return|goto|const|static|extern|auto|register|volatile|inline|restrict)\b/g, c: 'keyword' },
            { r: /#\s*(include|define|ifdef|ifndef|endif|pragma)/g, c: 'preprocessor' },
            { r: /\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b/g, c: 'number' }
        ],
        css: [
            { r: /(\/\*[\s\S]*?\*\/)/g, c: 'comment' },
            { r: /([.#][a-zA-Z][a-zA-Z0-9_-]*)/g, c: 'selector' },
            { r: /([a-zA-Z-]+)\s*:/g, c: 'property' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' },
            { r: /#[0-9a-fA-F]{3,8}\b/g, c: 'number' },
            { r: /\b(\d+(?:px|em|rem|%|vh|vw|pt)?)/g, c: 'number' }
        ],
        html: [
            { r: /(<!--[\s\S]*?-->)/g, c: 'comment' },
            { r: /(<\/?[a-zA-Z][a-zA-Z0-9]*)/g, c: 'tag' },
            { r: /([a-zA-Z-]+)=/g, c: 'attr' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' }
        ],
        ini: [
            { r: /(;.*$|#.*$)/gm, c: 'comment' },
            { r: /(\[[^\]]+\])/g, c: 'section' },
            { r: /^([a-zA-Z_][a-zA-Z0-9_.]*)(?=\s*=)/gm, c: 'property' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' }
        ],
        javascript: [
            { r: /([^:]\/\/.*$)/gm, c: 'comment' },
            { r: /(\/\*[\s\S]*?\*\/)/g, c: 'comment' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|`(?:\\.|[^`\\])*`)/g, c: 'string' },
            { r: /[^'"]\b([a-zA-Z0-9_]+)\s*:/g, c: 'property' },
            { r: /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|try|catch|throw|typeof|instanceof|delete|void|yield|break|continue|switch|case|default|do)\b/g, c: 'keyword' },
            { r: /\b(true|false|null|undefined|NaN|Infinity)\b/g, c: 'literal' },
            { r: /\b(\d+\.?\d*|\.\d+)\b/g, c: 'number' },
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
            // Standard libraries
            { r: /\b(string|table|math|os|io|coroutine|debug|package)\b/g, c: 'type' },
            { r: /\b(\d+\.?\d*|\.\d+|0x[0-9a-fA-F]+)\b/g, c: 'number' },
            { r: /\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?=\s*[,)=])/g, c: 'variable' },
            { r: /(\.\.\.|\.{2}|[+\-*\/%^#=<>~]+)/g, c: 'operator' }
        ],
        php: [
            { r: /(#.*$|\/\/.*$)/gm, c: 'comment' },
            { r: /(\/\*[\s\S]*?\*\/)/g, c: 'comment' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' },
            { r: /\b(class|function|public|private|protected|static|const|return|if|else|foreach|while|new|namespace|use|trait|interface|extends|implements|echo|print|require|include)\b/g, c: 'keyword' },
            { r: /(\$[a-zA-Z_][a-zA-Z0-9_]*)/g, c: 'variable' },
            { r: /\b(\d+\.?\d*|\.\d+)\b/g, c: 'number' }
        ],
        python: [
            { r: /(#.*$)/gm, c: 'comment' },
            { r: /('''[\s\S]*?'''|"""[\s\S]*?""")/g, c: 'string' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' },
            { r: /\b(def|class|import|from|return|if|elif|else|for|while|in|try|except|finally|with|as|lambda|yield|async|await|pass|break|continue|global|nonlocal)\b/g, c: 'keyword' },
            { r: /\b(True|False|None)\b/g, c: 'literal' },
            { r: /\b(\d+\.?\d*|\.\d+)\b/g, c: 'number' }
        ],
        shell: [
            { r: /(#.*$)/gm, c: 'comment' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' },
            { r: /^(#!.*)$/gm, c: 'preprocessor' },
            { r: /\b(if|then|else|elif|fi|for|while|until|do|done|case|esac|select|function|in)\b/g, c: 'keyword' },
            { r: /\b(echo|printf|read|cd|rm|exit|return|source|alias|unalias|export|unset|shift|getopts|test|sudo( [A-Za-z0-9_./-]+)?|chmod|chown|\[\])\b/g, c: 'builtin' },
            { r: /\b(ls|cat|grep|awk|sed|find|xargs|tail|head|screen|dtach|curl|wget|ssh|scp|tar|zip|unzip|make|defaults|docker|kubectl|tmutil|pbcopy|pbpaste|node|npm)\b/g, c: 'function' },
            { r: /(\$[a-zA-Z_][a-zA-Z0-9_]*|\${[^}]+})/g, c: 'variable' },
            { r: /\s(--[a-zA-Z0-9\-]+|\-[a-zA-Z]+)/g, c: 'flag' },
            // Pipes and redirects
            { r: /(\|\||&&|(?<!&[a-z]{2,5});|\||>>|>|<|2>>?|&>)/g, c: 'operator' },
            // Subshells and command substitution
            { r: /(\$\(|\)|`|\\\()/g, c: 'operator' },
            { r: /\b(\d+)\b/g, c: 'number' }
        ],
        text: [],
        typescript: [
            { r: /(\/\/.*$)/gm, c: 'comment' },
            { r: /(\/\*[\s\S]*?\*\/)/g, c: 'comment' },
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|`(?:\\.|[^`\\])*`)/g, c: 'string' },
            { r: /\b(const|let|var|function|return|if|else|for|while|class|import|export|from|async|await|new|this|super|extends|static|interface|type|enum|namespace|public|private|protected|readonly|implements|declare)\b/g, c: 'keyword' },
            { r: /\b(string|number|boolean|any|void|never|unknown)\b/g, c: 'type' },
            { r: /\b(true|false|null|undefined|NaN|Infinity)\b/g, c: 'literal' },
            { r: /\b(\d+\.?\d*|\.\d+)\b/g, c: 'number' }
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
            { r: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, c: 'string' }
        ]
    };

    // aliases
    patterns.bash = patterns.shell;
    patterns.plain = patterns.text;

    function highlightQuery(q) {
        // q starts as something like "?p=37139&amp;foo=bar"
        return q.replace(/^\?(.*)$/, function (_, rest) {
            var out = '<span class="fmc-url-query-mark">?</span>';
            if (!rest) return out;
    
            // split on the ALREADY ESCAPED & ("&amp;")
            var parts = rest.split(/(&amp;)/);
            for (var i = 0; i < parts.length; i++) {
                var part = parts[i];
                if (part === '&amp;') {
                    // render a single & (already safe in HTML)
                    out += '<span class="fmc-url-query-amp">&amp;</span>';
                } else if (part) {
                    var m = part.match(/^([^=]+)(=(.*))?$/);
                    if (m) {
                        var key = m[1];
                        var eq  = m[2] ? '=' : '';
                        var val = m[3] || '';
                        out += '<span class="fmc-url-query-key">' + key + '</span>';
                        if (eq) {
                            out += '<span class="fmc-url-query-eq">=</span>';
                        }
                        if (val) {
                            out += '<span class="fmc-url-query-value">' + val + '</span>';
                        }
                    } else {
                        out += part;
                    }
                }
            }
            return out;
        });
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
            // no .replace(/'/g, '&#039;')
    }

    
    function highlightCode(code, lang) {
        if (!patterns[lang]) lang = 'text';
    
        let html = escapeHtml(code);
        const tokens = [];
    
        patterns[lang].forEach(function (p) {
            html = html.replace(p.r, function (match) {
                const id = '___TOKEN_' + tokens.length + '___';
                tokens.push({ match: match, className: p.c });
                return id;
            });
        });
    
        tokens.forEach(function (token, i) {
            var placeholder = '___TOKEN_' + i + '___';
            var inner;

            if (lang === 'url' && token.className === 'url-query') {
                inner = highlightQuery(token.match);
            } else {
                inner = token.match;
            }

            html = html.split(placeholder).join(
                '<span class="fmc-' + token.className + '">' + inner + '</span>'
            );
        });

    
        // Final safety pass: if any placeholders remain, just show the text they represent
        html = html.replace(/___TOKEN_(\d+)___/g, function (_, idx) {
            const t = tokens[Number(idx)];
            return t ? t.match : '';
        });
    
        return html;
    }

    function attachScrollHints() {
        document.querySelectorAll('.fmc-wrapper').forEach(function (wrap) {
            var pre = wrap.querySelector('.fmc-pre');
            if (!pre) return;
    
            function update() {
                if (pre.scrollLeft > 0) {
                    wrap.classList.add('fmc-wrapper--left');
                } else {
                    wrap.classList.remove('fmc-wrapper--left');
                }
            }
    
            pre.addEventListener('scroll', update, { passive: true });
            update(); // run once on load
        });
    }

    function init() {
        document.querySelectorAll('.fmc-code').forEach(el => {
            const id = el.getAttribute('data-id') || 'fmc';
            const lang = el.getAttribute('data-lang') || 'javascript';
            const code = el.textContent;
            el.innerHTML = highlightCode(code, lang);
        });

        document.querySelectorAll('.fmc-copy').forEach(btn => {
            btn.addEventListener('click', function() {
                const code = this.closest('.fmc-wrapper').querySelector('.fmc-code').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    const original = this.textContent;
                    this.textContent = 'Copied!';
                    setTimeout(() => { this.textContent = original; }, 2000);
                });
                Matomo && Matomo.getAsyncTracker().trackEvent('copy', 'fmc-code', id);
            });
        });

        attachScrollHints();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
