(function() {
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var useEffect = wp.element.useEffect;
    var useRef = wp.element.useRef;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var TextareaControl = wp.components.TextareaControl;

    // Language options
    var languages = [
        { label: 'C', value: 'c' },
        { label: 'CSS', value: 'css' },
        { label: 'HTML', value: 'html' },
        { label: 'INI', value: 'ini' },
        { label: 'JavaScript', value: 'javascript' },
        { label: 'JSON', value: 'json' },
        { label: 'Lua', value: 'lua' },
        { label: 'PHP', value: 'php' },
        { label: 'Python', value: 'python' },
        { label: 'Shell', value: 'shell' },
        { label: 'Text', value: 'text' },
        { label: 'TypeScript', value: 'typescript' },
        { label: 'URL', value: 'url' },
        { label: 'XML', value: 'xml' }
    ];

    // Calculate optimal number of rows for textarea
    function calculateRows(code) {
        if (!code) return 3;
        
        // Count newlines in the code
        var lineCount = (code.match(/\n/g) || []).length + 1;
        
        // Apply min and max constraints
        var minRows = 3;
        var maxRows = 30;
        
        return Math.max(minRows, Math.min(maxRows, lineCount));
    }

    wp.blocks.registerBlockType('franklin/mini-codeblock', {
        edit: function(props) {
            var attr = props.attributes;
            var set = props.setAttributes;
            var textareaRef = useRef(null);

            // Auto-resize textarea based on content
            useEffect(function() {
                if (!textareaRef.current) return;
                
                var textarea = textareaRef.current.querySelector('textarea');
                if (!textarea) return;
                
                // Set rows attribute
                var rows = calculateRows(attr.code);
                textarea.rows = rows;
            }, [attr.code]);

            return el('div', useBlockProps(),
                el(TextControl, {
                    label: 'Matomo ID',
                    value: attr.id,
                    onChange: function(v) { set({ id: v }); }
                }),
                el(SelectControl, {
                    label: 'Language',
                    value: attr.language,
                    options: languages,
                    onChange: function(v) { set({ language: v }); }
                }),
                el('div', { ref: textareaRef },
                    el(TextareaControl, {
                        label: 'Code',
                        className: 'fmc-editor-textarea',
                        value: attr.code,
                        onChange: function(v) { set({ code: v }); },
                        rows: calculateRows(attr.code)
                    })
                )
            );
        },
        save: function() { return null; }
    });
})();
