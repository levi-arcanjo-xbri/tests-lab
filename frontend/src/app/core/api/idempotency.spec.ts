import { IdempotencyKeyHolder, generateUuid } from './idempotency';

describe('IdempotencyKeyHolder', () => {
  it('reaproveita a mesma chave até o reset (reenvio após falha)', () => {
    const holder = new IdempotencyKeyHolder();

    const first = holder.current();
    expect(holder.current()).toBe(first);

    holder.reset();
    expect(holder.current()).not.toBe(first);
  });
});

describe('generateUuid', () => {
  it('gera UUID v4 mesmo sem crypto.randomUUID (contexto não seguro)', () => {
    spyOnProperty(window, 'isSecureContext').and.returnValue(false);
    const original = crypto.randomUUID;
    Object.defineProperty(crypto, 'randomUUID', { value: undefined, configurable: true });

    try {
      expect(generateUuid()).toMatch(/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/);
    } finally {
      Object.defineProperty(crypto, 'randomUUID', { value: original, configurable: true });
    }
  });
});
