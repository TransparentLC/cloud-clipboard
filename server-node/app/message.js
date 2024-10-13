import config from './config.js';

export default {
    counter: 0,
    queue: [],
    /**
     * @param {*} item
     */
    enqueue(item) {
        this.queue.push(item);
        this.counter++;
        while (this.queue.length > config.server.history) this.queue.shift();
    },
    /**
     * @return {*}
     */
    dequeue() {
        this.queue.shift();
    },
};
