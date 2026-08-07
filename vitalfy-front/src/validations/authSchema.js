import { z } from 'zod';

export const registerSchema = z.object({
    name: z.string()
        .min(2, 'O nome deve ter no mínimo 2 caracteres')
        .max(50, 'O nome deve ter no máximo 50 caracteres'),
    email: z.string()
        .email('E-mail inválido'),
    password: z.string()
        .min(8, 'A senha deve ter no mínimo 8 caracteres')
        .regex(/[A-Z]/, 'A senha deve conter pelo menos uma letra maiúscula')
        .regex(/[a-z]/, 'A senha deve conter pelo menos uma letra minúscula')
        .regex(/[0-9]/, 'A senha deve conter pelo menos um número'),
    password_confirmation: z.string(),
    checked: z.boolean().refine(val => val === true, {
        message: 'Você deve aceitar os Termos de Uso e a Política de Privacidade',
    })
}).refine((data) => data.password === data.password_confirmation, {
    message: "As senhas não coincidem",
    path: ["password_confirmation"],
});

export const forgotPasswordSchema = z.object({
    email: z.string().email('E-mail inválido'),
});

export const resetPasswordSchema = z.object({
    password: z.string()
        .min(8, 'A senha deve ter no mínimo 8 caracteres')
        .regex(/[A-Z]/, 'A senha deve conter pelo menos uma letra maiúscula')
        .regex(/[a-z]/, 'A senha deve conter pelo menos uma letra minúscula')
        .regex(/[0-9]/, 'A senha deve conter pelo menos um número'),
    password_confirmation: z.string(),
}).refine((data) => data.password === data.password_confirmation, {
    message: 'As senhas não coincidem',
    path: ['password_confirmation'],
});