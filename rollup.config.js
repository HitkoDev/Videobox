import sourcemaps from 'rollup-plugin-sourcemaps'

export default {
    entry: 'build/index.js',
    plugins: [
        sourcemaps()
    ],
    sourceMap: true,
    targets: [
        {
            dest: 'build/videobox.js',
            sourceMap: true
        }
    ]
}