(function () {
    const registerBlockType = wp.blocks.registerBlockType;
    const el = wp.element.createElement;
    const TextareaControl = wp.components.TextareaControl;
    const SelectControl = wp.components.SelectControl;
    const TextControl = wp.components.TextControl;
    const useBlockProps = wp.blockEditor.useBlockProps;

    registerBlockType('franklin/mini-codeblock', {
        edit: function (props) {
            const attributes = props.attributes;
            const setAttributes = props.setAttributes;
            const blockProps = useBlockProps();

            const languages = [
                { label: 'JavaScript', value: 'javascript' },
                { label: 'TypeScript', value: 'typescript' },
                { label: 'CSS', value: 'css' },
                { label: 'HTML', value: 'html' },
                { label: 'PHP', value: 'php' },
                { label: 'Python', value: 'python' },
                { label: 'Lua', value: 'lua' },
                { label: 'JSON', value: 'json' },
                { label: 'XML', value: 'xml' },
                { label: 'Bash', value: 'bash' },
                { label: 'INI', value: 'ini' },
                { label: 'C', value: 'c' },
                { label: 'URL', value: 'url' }
            ];

            return el(
                'div',
                blockProps,
                el(TextControl, {
                  label: 'Matomo ID',
                  value: attributes.id,
                  onChange: function (value) {
                    setAttributes({ id: value });
                  }
                }),
                el(SelectControl, {
                    label: 'Language',
                    value: attributes.language,
                    options: languages,
                    onChange: function (value) {
                        setAttributes({ language: value });
                    }
                }),
                el(TextareaControl, {
                    label: 'Code',
                    className: 'fmc-editor-textarea',
                    value: attributes.code,
                    onChange: function (value) {
                        setAttributes({ code: value });
                    },
                    rows: 16
                })
            );
        },
        save: function () {
            return null;
        }
    });
})();
