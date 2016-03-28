interface webAnimation {
    addEventListener(event: string, callback: Function): void,
    cancel(): void,
}

interface HTMLElement {
    animate(a: any, b: any): webAnimation
}