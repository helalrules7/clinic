/** Payload when timepicker opens */
type OpenEventData = {
  hour: string;
  minutes: string;
  type?: string;
  degreesHours: number | null;
  degreesMinutes: number | null;
};

/** Payload when user cancels */
type CancelEventData = Record<string, never>;

/** Payload when user confirms time */
type ConfirmEventData = {
  hour?: string;
  minutes?: string;
  type?: string;
};

/** Payload when modal shows */
type ShowEventData = Record<string, never>;

/** Payload when modal hides */
type HideEventData = Record<string, never>;

/** Payload for real-time updates */
type UpdateEventData = {
  hour?: string;
  minutes?: string;
  type?: string;
};

/** Payload when hour mode activated */
type SelectHourEventData = {
  hour: string;
};

/** Payload when minute mode activated */
type SelectMinuteEventData = {
  minutes: string;
};

/** Payload when AM selected */
type SelectAMEventData = Record<string, never>;

/** Payload when PM selected */
type SelectPMEventData = Record<string, never>;

/** Payload when switching view */
type SwitchViewEventData = Record<string, never>;

/** Payload when validation error occurs */
type ErrorEventData = {
  error: string;
  rejectedHour?: string;
  rejectedMinute?: string;
  inputHour?: string | number;
  inputMinute?: string | number;
  inputType?: string;
  inputLength?: string | number;
};

/** Callback function for timepicker events */
type TimepickerEventCallback<T = unknown> = (eventData: T) => void;

type OptionTypes = {
  /** AM label text @default "AM" */
  amLabel?: string;
  /** Enable animations @default true */
  animation?: boolean;
  /**
   * @description Set default selector to append timepicker inside it. Timepicker default append to `body`
   * @default ""
   */
  appendModalSelector?: string;
  /** Enable backdrop @default true */
  backdrop?: boolean;
  /** Cancel button text @default "Cancel" */
  cancelLabel?: string;
  /** Edit hour/minutes in web mode @default false */
  editable?: boolean;
  /** Enable scroll when open @default false */
  enableScrollbar?: boolean;
  /** Icon to switch desktop/mobile @default false */
  enableSwitchIcon?: boolean;
  /** Mobile time label @default "Enter Time" */
  mobileTimeLabel?: string;
  /** Focus input after close @default false */
  focusInputAfterCloseModal?: boolean;
  /** Mobile hour label @default "Hour" */
  hourMobileLabel?: string;
  /** Desktop icon template */
  iconTemplate?: string;
  /** Mobile icon template */
  iconTemplateMobile?: string;
  /**
   * @description Set increment hour by `1`, `2`, `3` hour
   * @default 1
   */
  incrementHours?: number;
  /**
   * @description Set increment minutes by `1`, `5`, `10`, `15` minutes
   * @default 1
   */
  incrementMinutes?: number;
  /**
   * @description set custom text to minute label on mobile version
   * @default "Minute"
   */
  minuteMobileLabel?: string;
  /**
   * @description Turn on/off mobile version
   * @default false
   */
  mobile?: boolean;
  /**
   * @description Set custom text to ok label
   * @default "OK"
   */
  okLabel?: string;
  /** PM label @default "PM" */
  pmLabel?: string;
  /** Time label (desktop) @default "Select time" */
  timeLabel?: string;
  /** Auto-switch to minutes @default true */
  autoSwitchToMinutes?: boolean;
  /** Theme @default "basic" */
  theme?:
    | 'basic'
    | 'crane'
    | 'crane-straight'
    | 'm2'
    | 'm3-green'
    | 'dark'
    | 'glassmorphic'
    | 'pastel'
    | 'ai'
    | 'cyberpunk';
  /** Clock type: 12h or 24h @default "12h" */
  clockType?: '12h' | '24h';
  /** Disable specific times (hours/minutes/intervals) */
  disabledTime?: {
    minutes?: Array<string | number>;
    hours?: Array<string | number>;
    interval?: string | string[];
  };
  /** Current time config */
  /** Current time config */
  currentTime?:
    | {
        time?: Date;
        updateInput?: boolean;
        locales?: string | string[];
        preventClockType?: boolean;
      }
    | boolean;

  /** Focus trap on modal @default true */
  focusTrap?: boolean;
  /** Delay for clickable elements (ms) @default 300 */
  delayHandler?: number;
  /** Custom ID for instance */
  id?: string;
  /** Inline mode config */
  inline?: {
    enabled: boolean;
    containerId: string;
    /** @default false */
    showButtons?: boolean;
    /** @default true */
    autoUpdate?: boolean;
  };
  /** Additional CSS class */
  cssClass?: string;
  /** Callback when picker opens */
  onOpen?: TimepickerEventCallback<OpenEventData>;
  /** Callback when user cancels */
  onCancel?: TimepickerEventCallback<CancelEventData>;
  /** Callback when user confirms */
  onConfirm?: TimepickerEventCallback<ConfirmEventData>;
  /** Callback during interaction */
  onUpdate?: TimepickerEventCallback<UpdateEventData>;
  /** Callback when hour mode activated */
  onSelectHour?: TimepickerEventCallback<SelectHourEventData>;
  /** Callback when minute mode activated */
  onSelectMinute?: TimepickerEventCallback<SelectMinuteEventData>;
  /** Callback when AM selected */
  onSelectAM?: TimepickerEventCallback<SelectAMEventData>;
  /** Callback when PM selected */
  onSelectPM?: TimepickerEventCallback<SelectPMEventData>;
  /** Callback on validation error */
  onError?: TimepickerEventCallback<ErrorEventData>;
};

type EventHandler<T = unknown> = (data: T) => void;
interface TimepickerEventMap {
    open: OpenEventData;
    cancel: CancelEventData;
    confirm: ConfirmEventData;
    show: ShowEventData;
    hide: HideEventData;
    update: UpdateEventData;
    'select:hour': SelectHourEventData;
    'select:minute': SelectMinuteEventData;
    'select:am': SelectAMEventData;
    'select:pm': SelectPMEventData;
    'switch:view': SwitchViewEventData;
    'animation:clock': Record<string, never>;
    error: ErrorEventData;
    [key: string]: unknown;
}
declare class EventEmitter<EventMap extends Record<string, unknown> = TimepickerEventMap> {
    private events;
    on<K extends keyof EventMap>(event: K, handler: EventHandler<EventMap[K]>): void;
    once<K extends keyof EventMap>(event: K, handler: EventHandler<EventMap[K]>): void;
    off<K extends keyof EventMap>(event: K, handler?: EventHandler<EventMap[K]>): void;
    emit<K extends keyof EventMap>(event: K, data?: EventMap[K]): void;
    clear(): void;
    hasListeners<K extends keyof EventMap>(event: K): boolean;
}

/**
 * Grouped options structure for Timepicker UI v4.0.0
 *
 * BREAKING CHANGE from v3.x:
 * Options are now organized into logical groups for better clarity and maintainability.
 */



/**
 * Clock behavior configuration
 */
interface ClockOptions {
  /**
   * @description Set type of clock: `12h` or `24h`
   * @default "12h"
   */
  type?: '12h' | '24h';

  /**
   * @description Set increment for hours (1, 2, 3, etc.)
   * @default 1
   */
  incrementHours?: number;

  /**
   * @description Set increment for minutes (1, 5, 10, 15, etc.)
   * @default 1
   */
  incrementMinutes?: number;

  /**
   * @description Automatically switch to minutes after selecting hour
   * @default false
   */
  autoSwitchToMinutes?: boolean;

  /**
   * @description Disable specific hours/minutes or time intervals
   * @example
   * disabledTime: {
   *   minutes: [1, 2, 4, 5, 55, 23, "22", "38"],
   *   hours: [1, "3", "5", 8],
   *   interval: "10:00 AM - 12:00 PM" | ["10:00 AM - 12:00 PM", "5:00 PM - 8:00 PM"]
   * }
   */
  disabledTime?: {
    minutes?: Array<string | number>;
    hours?: Array<string | number>;
    interval?: string | string[];
  };

  /**
   * @description Set current time to the input and timepicker
   * @example
   * currentTime: {
   *   time: new Date(),
   *   updateInput: true,
   *   locales: "en-US",
   *   preventClockType: false
   * }
   * @example currentTime: true
   */
  currentTime?:
    | {
        time?: Date;
        updateInput?: boolean;
        locales?: string | string[];
        preventClockType?: boolean;
      }
    | boolean;
}

/**
 * UI appearance and behavior configuration
 */
interface UIOptions {
  /**
   * @description Theme for the timepicker
   * @default "basic"
   */
  theme?:
    | 'basic'
    | 'crane'
    | 'crane-straight'
    | 'm2'
    | 'm3-green'
    | 'dark'
    | 'glassmorphic'
    | 'pastel'
    | 'ai'
    | 'cyberpunk';

  /**
   * @description Enable/disable animations
   * @default true
   */
  animation?: boolean;

  /**
   * @description Show backdrop when timepicker is open
   * @default true
   */
  backdrop?: boolean;

  /**
   * @description Force mobile mode
   * @default false
   */
  mobile?: boolean;

  /**
   * @description Enable switch icon (mobile ↔ desktop)
   * @default false
   */
  enableSwitchIcon?: boolean;

  /**
   * @description Allow editing hour/minutes directly
   * @default false
   */
  editable?: boolean;

  /**
   * @description Enable scrollbar when timepicker is open
   * @default false
   */
  enableScrollbar?: boolean;

  /**
   * @description Additional CSS class for the wrapper
   * @example cssClass: "my-custom-timepicker"
   */
  cssClass?: string;

  /**
   * @description Selector where to append modal (default: body)
   * @default ""
   */
  appendModalSelector?: string;

  /**
   * @description Custom keyboard icon template (desktop → mobile)
   * @default Material Icons template
   */
  iconTemplate?: string;

  /**
   * @description Custom schedule icon template (mobile → desktop)
   * @default Material Icons template
   */
  iconTemplateMobile?: string;

  /**
   * @description Inline mode configuration
   * @example
   * inline: {
   *   enabled: true,
   *   containerId: "timepicker-container",
   *   showButtons: false,
   *   autoUpdate: true
   * }
   */
  inline?: {
    enabled: boolean;
    containerId: string;
    showButtons?: boolean;
    autoUpdate?: boolean;
  };
}

/**
 * Text labels configuration
 */
interface LabelsOptions {
  /**
   * @description "AM" label text
   * @default "AM"
   */
  am?: string;

  /**
   * @description "PM" label text
   * @default "PM"
   */
  pm?: string;

  /**
   * @description "OK" button text
   * @default "OK"
   */
  ok?: string;

  /**
   * @description "Cancel" button text
   * @default "Cancel"
   */
  cancel?: string;

  /**
   * @description Time label on desktop
   * @default "Select time"
   */
  time?: string;

  /**
   * @description Time label on mobile
   * @default "Enter Time"
   */
  mobileTime?: string;

  /**
   * @description Hour label on mobile
   * @default "Hour"
   */
  mobileHour?: string;

  /**
   * @description Minute label on mobile
   * @default "Minute"
   */
  mobileMinute?: string;
}

/**
 * Behavior configuration
 */
interface BehaviorOptions {
  /**
   * @description Focus input after closing modal
   * @default false
   */
  focusInputAfterClose?: boolean;

  /**
   * @description Enable focus trap in modal
   * @default true
   */
  focusTrap?: boolean;

  /**
   * @description Delay for clickable elements (ms)
   * @default 300
   */
  delayHandler?: number;

  /**
   * @description Custom ID for the timepicker instance
   * @example id: "my-timepicker-1"
   */
  id?: string;
}

/**
 * Event callbacks configuration
 */
interface CallbacksOptions {
  /**
   * @description Triggered when timepicker opens
   */
  onOpen?: TimepickerEventCallback<OpenEventData>;

  /**
   * @description Triggered when user cancels
   */
  onCancel?: TimepickerEventCallback<CancelEventData>;

  /**
   * @description Triggered when user confirms time
   */
  onConfirm?: TimepickerEventCallback<ConfirmEventData>;

  /**
   * @description Triggered during interaction (real-time)
   */
  onUpdate?: TimepickerEventCallback<UpdateEventData>;

  /**
   * @description Triggered when hour mode is selected
   */
  onSelectHour?: TimepickerEventCallback<SelectHourEventData>;

  /**
   * @description Triggered when minute mode is selected
   */
  onSelectMinute?: TimepickerEventCallback<SelectMinuteEventData>;

  /**
   * @description Triggered when AM is selected
   */
  onSelectAM?: TimepickerEventCallback<SelectAMEventData>;

  /**
   * @description Triggered when PM is selected
   */
  onSelectPM?: TimepickerEventCallback<SelectPMEventData>;

  /**
   * @description Triggered on invalid time format
   */
  onError?: TimepickerEventCallback<ErrorEventData>;
}

/**
 * Main options type with grouped structure (v4.0.0)
 */
interface TimepickerOptions {
  clock?: ClockOptions;
  ui?: UIOptions;
  labels?: LabelsOptions;
  behavior?: BehaviorOptions;
  callbacks?: CallbacksOptions;
}

type Callback = () => void;
declare class TimepickerUI {
    private readonly core;
    private readonly managers;
    private readonly lifecycle;
    private readonly emitter;
    constructor(selectorOrElement: string | HTMLElement, options?: TimepickerOptions);
    private setupInternalEventListeners;
    create(): void;
    open(callback?: Callback): void;
    close(update?: boolean, callback?: Callback): void;
    destroy(options?: {
        keepInputValue?: boolean;
        callback?: Callback;
    } | Callback): void;
    update(value: {
        options: TimepickerOptions;
        create?: boolean;
    }, callback?: Callback): void;
    getValue(): {
        hour: string;
        minutes: string;
        type?: string;
        time: string;
        degreesHours: number | null;
        degreesMinutes: number | null;
    };
    setValue(time: string, updateInput?: boolean): void;
    getElement(): HTMLElement;
    get instanceId(): string;
    get options(): Required<TimepickerOptions>;
    get isInitialized(): boolean;
    get isDestroyed(): boolean;
    get hour(): HTMLInputElement | null;
    get minutes(): HTMLInputElement | null;
    get okButton(): HTMLButtonElement | null;
    get cancelButton(): HTMLButtonElement | null;
    get clockHand(): HTMLDivElement | null;
    on<K extends keyof TimepickerEventMap>(event: K, handler: (data: TimepickerEventMap[K]) => void): void;
    once<K extends keyof TimepickerEventMap>(event: K, handler: (data: TimepickerEventMap[K]) => void): void;
    off<K extends keyof TimepickerEventMap>(event: K, handler?: (data: TimepickerEventMap[K]) => void): void;
    private resolveInputElement;
    private createWrapperElement;
    static getById(id: string): TimepickerUI | undefined;
    static getAllInstances(): TimepickerUI[];
    static isAvailable(selectorOrElement: string | HTMLElement): boolean;
    static destroyAll(): void;
}

export { type BehaviorOptions, type CallbacksOptions, type CancelEventData, type ClockOptions, type ConfirmEventData, type ErrorEventData, EventEmitter, type HideEventData, type LabelsOptions, type OpenEventData, type OptionTypes, type SelectAMEventData, type SelectHourEventData, type SelectMinuteEventData, type SelectPMEventData, type ShowEventData, type SwitchViewEventData, type TimepickerOptions, TimepickerUI, type UIOptions, type UpdateEventData, TimepickerUI as default };
