interface DocumentTimelineOptions {
	originTime?: DOMHighResTimeStamp;
}

interface AnimationEffectTimingProperties {
	delay?: number;
	endDelay?: number;
	fill?: FillMode;
	iterationStart?: number;
	iterations?: number;
	duration?: number | string;
	direction?: PlaybackDirection;
	easing?: string;
}

interface ComputedTimingProperties extends AnimationEffectTimingProperties {
	endTime?: number;
	activeDuration?: number;
	localTime?: number;
	progress?: number;
	currentIteration?: number;
}

interface BaseComputedKeyframe {
	offset?: number;
	computedOffset?: number;
	easing?: string;
	composite?: CompositeOperation;
}

interface BasePropertyIndexedKeyframe {
	easing?: string;
	composite?: CompositeOperation;
}

interface BaseKeyframe {
	offset?: number;
	easing?: string;
	composite?: CompositeOperation;
}

interface KeyframeEffectOptions extends AnimationEffectTimingProperties {
	iterationComposite?: IterationCompositeOperation;
	composite?: CompositeOperation;
	spacing?: string;
}

interface KeyframeAnimationOptions extends KeyframeEffectOptions {
	id?: string;
}

interface AnimationPlaybackEventInit extends EventInit {
	currentTime?: number;
	timelineTime?: number;
}

interface AnimationTimeline {
	readonly currentTime?: number;
}

declare class DocumentTimeline implements AnimationTimeline {
	constructor(options: DocumentTimelineOptions);
	readonly currentTime?: number;
}

declare class Animation extends EventTarget {
	constructor(effect: AnimationEffectReadOnly, timeline: AnimationTimeline);
	id?: string;
	effect?: AnimationEffectReadOnly;
	timeline?: AnimationTimeline;
	startTime?: number;
	currentTime?: number;
	playbackRate?: number;
	readonly playState?: AnimationPlayState;
	readonly ready?: Promise<Animation>;
	readonly finished?: Promise<Animation>;
	onfinish?: EventHandler;
	oncancel?: EventHandler;
	cancel(): void;
	finish(): void;
	play(): void;
	pause(): void;
	reverse(): void;
}

interface AnimationEffectReadOnly {
	readonly timing?: AnimationEffectTimingReadOnly;
	getComputedTiming(): ComputedTimingProperties;
}

interface AnimationEffectTimingReadOnly {
	readonly delay?: number;
	readonly endDelay?: number;
	readonly fill?: FillMode;
	readonly iterationStart?: number;
	readonly iterations?: number;
	readonly duration?: number | string;
	readonly direction?: PlaybackDirection;
	readonly easing?: string;
}

interface AnimationEffectTiming extends AnimationEffectTimingReadOnly {
	delay?: number;
	endDelay?: number;
	fill?: FillMode;
	iterationStart?: number;
	iterations?: number;
	duration?: number | string;
	direction?: PlaybackDirection;
	easing?: string;
}

declare class KeyframeEffectReadOnly implements AnimationEffectReadOnly {
	constructor(target: Animatable, keyframes: any, options: number | KeyframeEffectOptions);
	readonly target?: Animatable;
	readonly iterationComposite?: IterationCompositeOperation;
	readonly composite?: CompositeOperation;
	readonly spacing?: string;
	readonly timing?: AnimationEffectTimingReadOnly;
	getKeyframes(): Array<any>;
	getComputedTiming(): ComputedTimingProperties;
}

declare class KeyframeEffect extends KeyframeEffectReadOnly {
	constructor(target: Animatable, keyframes: any, options: number | KeyframeEffectOptions);
	target?: Animatable;
	iterationComposite?: IterationCompositeOperation;
	composite?: CompositeOperation;
	spacing?: string;
	setKeyframes(keyframes: any): void;
}

declare class SharedKeyframeList {
	constructor(keyframes: any);
}

interface Animatable {
	animate(keyframes: any, options: number | KeyframeAnimationOptions): Animation;
	getAnimations(): Array<Animation>;
}

interface Document {
	readonly timeline?: DocumentTimeline;
	getAnimations(): Array<Animation>;
}

declare class AnimationPlaybackEvent extends Event {
	constructor(type: string, eventInitDict: AnimationPlaybackEventInit);
	readonly currentTime?: number;
	readonly timelineTime?: number;
}

interface HTMLElement extends Animatable { }

declare type AnimationPlayState = "idle" | "pending" | "running" | "paused" | "finished";
declare type FillMode = "none" | "forwards" | "backwards" | "both" | "auto";
declare type PlaybackDirection = "normal" | "reverse" | "alternate" | "alternate-reverse";
declare type IterationCompositeOperation = "replace" | "accumulate";
declare type CompositeOperation = "replace" | "add" | "accumulate";

declare type DOMHighResTimeStamp = number;
declare type EventHandler = (event: Event) => any;